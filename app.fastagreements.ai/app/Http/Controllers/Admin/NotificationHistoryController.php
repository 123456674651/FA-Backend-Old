<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NotificationHistory;
use App\Models\NotificationHistoryUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class NotificationHistoryController extends Controller
{
    /**
     * Display a listing of the notification history logs.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $type = $request->input('notification_type');
        $status = $request->input('status');
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');

        $query = NotificationHistory::with('sender');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%");
            });
        }

        if (!empty($type)) {
            $query->where('notification_type', $type);
        }

        if (!empty($status)) {
            $query->where('status', $status);
        }

        if (!empty($fromDate) && !empty($toDate)) {
            $query->whereBetween('created_at', [
                Carbon::parse($fromDate)->startOfDay(),
                Carbon::parse($toDate)->endOfDay()
            ]);
        }

        $logs = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        $types = [
            'new_template' => 'New Template',
            'offer' => 'Offer',
            'payment_success' => 'Payment Success',
            'payment_failed' => 'Payment Failed',
            'agreement_ready' => 'Agreement Ready',
            'reminder' => 'Reminder'
        ];

        return view('admin.notifications.history.index', compact(
            'logs', 'types', 'search', 'type', 'status', 'fromDate', 'toDate'
        ));
    }

    /**
     * Display details of a specific notification and its recipient list.
     */
    public function show(Request $request, $id)
    {
        $log = NotificationHistory::with('sender')->findOrFail($id);
        
        $search = $request->input('search');
        $status = $request->input('status');

        $recipientsQuery = NotificationHistoryUser::with('customer')
            ->where('notification_history_id', $id);

        if (!empty($status)) {
            $recipientsQuery->where('delivery_status', $status);
        }

        if (!empty($search)) {
            $recipientsQuery->whereHas('customer', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('mobile', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $recipients = $recipientsQuery->paginate(15)->withQueryString();

        return view('admin.notifications.history.show', compact('log', 'recipients', 'status', 'search'));
    }

    /**
     * Resend an existing push campaign.
     */
    public function resend($id)
    {
        $originalLog = NotificationHistory::findOrFail($id);

        // Fetch original recipients
        $originalRecipients = NotificationHistoryUser::where('notification_history_id', $id)->get();

        if ($originalRecipients->isEmpty()) {
            return redirect()->back()->with('error', 'Cannot resend: No recipients found in original campaign.');
        }

        DB::beginTransaction();
        try {
            // Create cloned history record
            $newCampaign = NotificationHistory::create([
                'title' => $originalLog->title,
                'message' => $originalLog->message,
                'image' => $originalLog->image,
                'notification_type' => $originalLog->notification_type,
                'total_recipients' => $originalLog->total_recipients,
                'success_count' => 0,
                'failed_count' => 0,
                'sent_by' => auth()->id(),
                'status' => 'sending'
            ]);

            // Save cloned recipients list
            $recipientRecords = [];
            foreach ($originalRecipients as $recipient) {
                $recipientRecords[] = [
                    'notification_history_id' => $newCampaign->id,
                    'customer_id' => $recipient->customer_id,
                    'fcm_token' => $recipient->fcm_token,
                    'delivery_status' => 'pending',
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }

            foreach (array_chunk($recipientRecords, 500) as $chunk) {
                NotificationHistoryUser::insert($chunk);
            }

            DB::commit();

            // Dispatch background sending campaign job
            \App\Jobs\SendPushNotificationJob::dispatch($newCampaign->id);

            return redirect()->route('notification-history.index')
                ->with('success', 'Push notification resent campaign successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to resend campaign: ' . $e->getMessage());
        }
    }

    /**
     * Delete notification history log record.
     */
    public function destroy($id)
    {
        $log = NotificationHistory::findOrFail($id);
        
        // Delete uploaded notification image if custom uploaded
        if (!empty($log->image) && file_exists(public_path($log->image))) {
            // Only delete if it's not referenced by a template
            $isReferencedByTemplate = \App\Models\NotificationTemplate::where('image', $log->image)->exists();
            if (!$isReferencedByTemplate) {
                @unlink(public_path($log->image));
            }
        }

        $log->delete(); // Cascades delete to notification_history_users

        return redirect()->route('notification-history.index')
            ->with('success', 'Campaign log successfully deleted.');
    }
}
