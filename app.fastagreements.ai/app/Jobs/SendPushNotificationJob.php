<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\NotificationHistory;
use App\Models\NotificationHistoryUser;
use App\Services\PushNotificationService;
use Illuminate\Support\Facades\Log;

class SendPushNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $historyId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $historyId)
    {
        $this->historyId = $historyId;
    }

    /**
     * Execute the job.
     */
    public function handle(PushNotificationService $service): void
    {
        $history = NotificationHistory::find($this->historyId);
        if (!$history) {
            Log::error("SendPushNotificationJob: Notification history ID {$this->historyId} not found.");
            return;
        }

        // Retrieve OAuth2 Token once for the entire batch
        $accessToken = $service->getFcmAccessToken();
        if (!$accessToken) {
            $history->update([
                'status' => 'failed',
                'failed_count' => $history->total_recipients
            ]);
            NotificationHistoryUser::where('notification_history_id', $this->historyId)
                ->update([
                    'delivery_status' => 'failed',
                    'failure_reason' => 'Firebase credentials or access token configuration issue.'
                ]);
            return;
        }

        $history->update(['status' => 'sending']);

        $successCount = 0;
        $failedCount = 0;

        // Process recipients in chunks of 100 for high scalability
        NotificationHistoryUser::where('notification_history_id', $this->historyId)
            ->chunk(100, function ($recipients) use ($service, $accessToken, $history, &$successCount, &$failedCount) {
                foreach ($recipients as $recipient) {
                    $token = $recipient->fcm_token;
                    
                    if (empty($token)) {
                        $recipient->update([
                            'delivery_status' => 'failed',
                            'failure_reason' => 'No FCM registration token registered for customer.'
                        ]);
                        $failedCount++;
                        continue;
                    }

                    try {
                        // Deliver via Firebase V1 HTTP API
                        $response = $service->sendFcmNotification(
                            $accessToken,
                            $token,
                            $history->title,
                            $history->message,
                            $history->image
                        );

                        $recipient->update([
                            'delivery_status' => 'success',
                            'firebase_response' => $response
                        ]);
                        $successCount++;
                    } catch (\Exception $e) {
                        $recipient->update([
                            'delivery_status' => 'failed',
                            'failure_reason' => $e->getMessage()
                        ]);
                        $failedCount++;
                    }
                }

                // Intermediate database status save to show progress
                $history->update([
                    'success_count' => $successCount,
                    'failed_count' => $failedCount
                ]);
            });

        // Set final completed status
        $history->update([
            'status' => 'completed',
            'success_count' => $successCount,
            'failed_count' => $failedCount
        ]);
    }
}
