<?php

namespace App\Http\Controllers\api\V2;

use App\Http\Controllers\api\BaseController;
use App\Models\ApplicationVersionUpdate;
use App\Models\Device;
use App\Models\DownloadApplicationUser;
use App\Models\User;
use App\Models\UserActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

class VersionController extends BaseController
{
    public function check_version(Request $request)
    {
        // 1. Validation: Ensure version is provided
        if (empty($request->application_version)) {
            $response = response()->json(['status' => false, 'message' => 'Missing version.'], 200);
            return $response;
        }

        $deviceType = Str::lower($request->input('device_type', 'ios'));
        $token = $request->device_token ?? "";
        $userId = $request->user_id;
        $userVersion = $request->application_version;

        // 2. Database Updates: Track user and device app version
        DownloadApplicationUser::updateOrCreate(
            ['device_token' => $token, 'device_type' => $deviceType],
            [
                'application_version' => $userVersion,
                'user_id' => $userId,
                'unique_id' => $this->generateUniqueId()
            ]
        );

        if (!empty($userId)) {
            User::where('id', $userId)->update([
                'application_version' => $userVersion
            ]);
        }

        Device::updateOrCreate(
            ['device_type' => $deviceType, 'device_token' => $token],
            ['device_type' => $deviceType]
        );

        // 3. Fetch current Live Version data from database
        $liveVersion = ApplicationVersionUpdate::where([
            'version_store_status' => 'is_live',
            'application_platform' => $deviceType,
            'is_deleted' => 0
        ])->orderByDesc('application_version_id')->first();

        // store user activity
        $this->user_activities_store($request);

        // Default safe values if no live version is configured in DB
        if (!$liveVersion) {
            $response = $this->sendApplicationMaintanceResponse("is_normal_update", "is_normal_mode", 1, 'Success', 0, config('app.GOOGLE_ADS_ACTIVE'), 0);
            Log::info('Version Check Response', ['response' => json_decode($response->getContent(), true)]);
            return $response;
        }

        // 4. Determine Update Status: Dynamic comparison (User vs Live)
        $isGreaterOrEqual = version_compare($userVersion, $liveVersion->application_version_name, '>=');

        // Check if the user's current version is still marked as live
        $userVersionRecord = ApplicationVersionUpdate::where([
            'application_version_name' => $userVersion,
            'application_platform' => $deviceType,
            'is_deleted' => 0
        ])->first();

        $isUserVersionLive = $userVersionRecord && $userVersionRecord->version_store_status === 'is_live';

        // CHANGE: If User has latest version or their version is still live, show "is_normal_update"
        $updateStatus = ($isGreaterOrEqual || $isUserVersionLive)
            ? "is_normal_update"
            : ($liveVersion->force_update_status ?? "is_force_updated");

        // 5. Handle Ads Logic
        $googleAdsActive = (bool) config('app.GOOGLE_ADS_ACTIVE');
        if (!empty($userId)) {
            $user = User::find($userId);
            if ($user) {
                if ($user->created_at && $user->created_at->diffInDays(now('UTC')) <= 10) {
                    $googleAdsActive = false;
                } else if ($user->plan_expires_at) {
                    $expiry = Carbon::parse($user->plan_expires_at);
                    if ($expiry->isPast()) {
                        $user->update([
                            'is_ads_active' => 1,
                            'is_subscription_active' => 0
                        ]);
                        $googleAdsActive = true;
                    } else {
                        $user->update([
                            'is_subscription_active' => 1
                        ]);
                        $googleAdsActive = false;
                    }
                } else {
                    $googleAdsActive = (bool) $user->is_ads_active;
                }
            }
        }

        if (in_array($userId, [123, 456, 789])) {
            $googleAdsActive = false;
        }

        // 6. Determine Response Code based on Application Mode
        $responseCode = match ($liveVersion->application_version_mode) {
            'is_normal_mode' => 200,
            'is_testing_mode' => 2001,
            'is_store_review_mode' => 3001,
            'is_maintance_mode' => ($liveVersion->maintenance_mode == 1) ? 1001 : 5001,
            default => 200,
        };

        if ($updateStatus == 'is_partial_updated') {
            $updateStatus = 'is_updated';
        }

        // 7. Return Final Response with all keys
        $response = $this->sendApplicationMaintanceResponse(
            $updateStatus,
            $liveVersion->application_version_mode,
            $responseCode,
            'Success',
            0,
            $googleAdsActive,
            (int) $liveVersion->maintenance_mode
        );

        return $response;
    }

    public function user_activities_store($request)
    {
        // Use UTC date to ensure a consistent "Daily" reset globally
        $today = now('UTC')->toDateString();

        if (!empty($request->user_id)) {
            // Find or create based on User + Date
            $activity = UserActivity::where('user_id', $request->user_id)
                ->whereDate('created_at', $today)
                ->first();

            if (!$activity) {
                $activity = UserActivity::create([
                    'user_id' => $request->user_id,
                    'counter' => 0,
                    'created_at' => now('UTC')
                ]);
            }
        } else {
            $tokenHash = hash('sha256', $request->device_token);

            // Find or create based on Hash + Date
            // We use a manual query here to ensure we target the DATE part of created_at
            $activity = UserActivity::where('device_token_hash', $tokenHash)
                ->whereDate('created_at', $today)
                ->first();

            if (!$activity) {
                $activity = UserActivity::create([
                    'device_token_hash' => $tokenHash,
                    'device_token' => $request->device_token,
                    'counter' => 0,
                    'created_at' => now('UTC') // Explicitly set UTC
                ]);
            }
        }

        $activity->increment('counter');

        return response()->json([
            'status' => true,
            'message' => 'Daily activity updated (UTC).',
            'utc_date' => $today,
            'current_counter' => $activity->counter
        ]);
    }
}