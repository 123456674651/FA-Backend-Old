<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\NotificationHistory;
use App\Models\NotificationHistoryUser;
use App\Models\Setting;
use App\Jobs\SendPushNotificationJob;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PushNotificationService
{
    /**
     * Get the query of customers matching the filters.
     *
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getFilteredCustomersQuery(array $filters)
    {
        $query = Customer::query();

        // 1. Audience Filter
        $audienceType = $filters['audience_type'] ?? 'all';

        if ($audienceType === 'category' && !empty($filters['target_category_id'])) {
            $catId = $filters['target_category_id'];
            $query->where(function ($q) use ($catId) {
                $q->whereHas('party1Agreements', function ($q2) use ($catId) {
                    $q2->where('category_id', $catId);
                })->orWhereHas('party2Agreements', function ($q2) use ($catId) {
                    $q2->where('category_id', $catId);
                });
            });
        } elseif ($audienceType === 'state' && !empty($filters['target_state_id'])) {
            $query->where('state_id', $filters['target_state_id']);
        } elseif ($audienceType === 'city' && !empty($filters['target_city_id'])) {
            $query->where('city_id', $filters['target_city_id']);
        } elseif ($audienceType === 'new_users') {
            if (!empty($filters['reg_from_date']) && !empty($filters['reg_to_date'])) {
                $query->whereBetween('created_at', [
                    Carbon::parse($filters['reg_from_date'])->startOfDay(),
                    Carbon::parse($filters['reg_to_date'])->endOfDay()
                ]);
            } else {
                $query->where('created_at', '>=', Carbon::now()->subDays(30));
            }
        }

        // 2. Additional Filters
        if (!empty($filters['customer_id'])) {
            $query->where('id', $filters['customer_id']);
        }

        if (!empty($filters['language_id'])) {
            $langId = $filters['language_id'];
            $query->where(function ($q) use ($langId) {
                $q->whereHas('party1Agreements', function ($q2) use ($langId) {
                    $q2->where('aggriment_language_id', $langId);
                })->orWhereHas('party2Agreements', function ($q2) use ($langId) {
                    $q2->where('aggriment_language_id', $langId);
                });
            });
        }

        if (isset($filters['active_users']) && $filters['active_users'] !== '') {
            $query->where('is_active', $filters['active_users']);
        }

        if (!empty($filters['from_date']) && !empty($filters['to_date'])) {
            $query->whereBetween('created_at', [
                Carbon::parse($filters['from_date'])->startOfDay(),
                Carbon::parse($filters['to_date'])->endOfDay()
            ]);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('mobile', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        return $query;
    }

    /**
     * Get Google OAuth2 Access Token for Firebase Cloud Messaging.
     *
     * @return string|bool
     */
    public function getFcmAccessToken()
    {
        $serviceAccountName = Setting::get('firebase_service_account');
        $projectId = Setting::get('firebase_project_id');

        if (!$serviceAccountName || !$projectId) {
            Log::warning('FCM Access Token: skipped credentials config missing.');
            return false;
        }

        $serviceAccountPath = storage_path('app/firebase/' . $serviceAccountName);
        if (!file_exists($serviceAccountPath)) {
            Log::warning("FCM Access Token: service account file not found at " . $serviceAccountPath);
            return false;
        }

        try {
            $json = json_decode(file_get_contents($serviceAccountPath), true);
            if (!$json || !isset($json['private_key']) || !isset($json['client_email'])) {
                throw new \Exception("Invalid service account JSON structure.");
            }

            $privateKey = $json['private_key'];
            $clientEmail = $json['client_email'];

            $header = json_encode(['alg' => 'RS256', 'typ' => 'JWT']);
            
            $now = time();
            $payload = json_encode([
                'iss' => $clientEmail,
                'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
                'aud' => 'https://oauth2.googleapis.com/token',
                'exp' => $now + 3600,
                'iat' => $now
            ]);

            $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
            $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));

            $signature = '';
            $success = openssl_sign(
                $base64UrlHeader . "." . $base64UrlPayload,
                $signature,
                $privateKey,
                'SHA256'
            );

            if (!$success) {
                throw new \Exception("Failed to sign JWT with private key.");
            }

            $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
            $jwt = $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;

            // Exchange JWT for Access Token
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://oauth2.googleapis.com/token');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt
            ]));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/x-www-form-urlencoded'
            ]);

            $response = curl_exec($ch);
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpcode !== 200) {
                throw new \Exception("OAuth2 token exchange failed: " . $response);
            }

            $responseData = json_decode($response, true);
            return $responseData['access_token'] ?? false;
        } catch (\Exception $e) {
            Log::error('FCM OAuth Token Generation Error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send push notification to a specific token.
     *
     * @param string $accessToken
     * @param string $fcmToken
     * @param string $title
     * @param string $body
     * @param string|null $imageUrl
     * @return string
     * @throws \Exception
     */
    public function sendFcmNotification(string $accessToken, string $fcmToken, string $title, string $body, ?string $imageUrl = null)
    {
        $projectId = Setting::get('firebase_project_id');
        $url = 'https://fcm.googleapis.com/v1/projects/' . $projectId . '/messages:send';
        
        $fields = [
            'message' => [
                'token' => $fcmToken,
                'notification' => [
                    'title' => $title,
                    'body' => $body
                ],
                'data' => [
                    'title' => $title,
                    'body' => $body,
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK'
                ],
                'android' => [
                    'notification' => [
                        'sound' => 'default',
                        'click_action' => 'TOP_STORY_ACTIVITY'
                    ]
                ],
                'apns' => [
                    'payload' => [
                        'aps' => [
                            'sound' => 'default',
                            'badge' => 1
                        ]
                    ]
                ]
            ]
        ];

        if (!empty($imageUrl)) {
            $fields['message']['notification']['image'] = $imageUrl;
            $fields['message']['data']['image'] = $imageUrl;
        }

        $headers = [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        
        $result = curl_exec($ch);
        $fcmHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($fcmHttpCode !== 200) {
            throw new \Exception("FCM HTTP v1 returned status code: " . $fcmHttpCode . ". Response: " . $result);
        }

        return $result;
    }

    /**
     * Send scheduled notifications.
     */
    public function sendScheduledNotifications()
    {
        $scheduled = NotificationHistory::where('status', 'scheduled')
            ->where('scheduled_at', '<=', Carbon::now())
            ->get();

        foreach ($scheduled as $history) {
            $history->update(['status' => 'sending']);
            SendPushNotificationJob::dispatch($history->id);
        }
    }
}
