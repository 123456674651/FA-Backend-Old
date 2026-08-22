<?php

if (!function_exists('setting')) {
    /**
     * Get setting value by key.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    function setting(string $key, $default = null)
    {
        return \App\Models\Setting::get($key, $default);
    }
}

if (!function_exists('send_push_notification')) {
    /**
     * Send FCM push notification using FCM HTTP v1.
     *
     * @param string $token Device token
     * @param string $title
     * @param string $body
     * @return string|bool Response result or false
     */
    function send_push_notification(string $token, string $title, string $body)
    {
        $serviceAccountName = \App\Models\Setting::get('firebase_service_account');
        $projectId = \App\Models\Setting::get('firebase_project_id');

        if (!$serviceAccountName || !$projectId) {
            \Illuminate\Support\Facades\Log::warning('FCM Push Notification skipped: credentials not fully configured.');
            return false;
        }

        $serviceAccountPath = storage_path('app/firebase/' . $serviceAccountName);
        if (!file_exists($serviceAccountPath)) {
            \Illuminate\Support\Facades\Log::warning("FCM Push Notification skipped: Service account file not found at " . $serviceAccountPath);
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
            $accessToken = $responseData['access_token'] ?? null;
            if (!$accessToken) {
                throw new \Exception("Access token not returned from token endpoint.");
            }

            // Send FCM Notification
            $url = 'https://fcm.googleapis.com/v1/projects/' . $projectId . '/messages:send';
            
            $fields = [
                'message' => [
                    'token' => $token,
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
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('FCM Push Notification error: ' . $e->getMessage());
            return false;
        }
    }
}
