<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NotificationTemplate;
use App\Models\NotificationHistory;
use App\Models\NotificationHistoryUser;
use App\Models\Customer;
use App\Services\PushNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PushNotificationApiController extends Controller
{
    protected $notificationService;

    public function __construct(PushNotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    // --- TEMPLATE CRUD ENDPOINTS ---

    /**
     * List notification templates.
     */
    public function listTemplates(Request $request)
    {
        $search = $request->input('search');
        $type = $request->input('notification_type');

        $query = NotificationTemplate::query();

        if (!empty($search)) {
            $query->where('title', 'like', "%{$search}%");
        }

        if (!empty($type)) {
            $query->where('notification_type', $type);
        }

        $templates = $query->orderBy('created_at', 'desc')->paginate(15);

        return response()->json([
            'status' => true,
            'message' => 'Templates retrieved successfully.',
            'data' => $templates
        ]);
    }

    /**
     * Store new template.
     */
    public function storeTemplate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'notification_type' => 'required|string|in:new_template,offer,payment_success,payment_failed,agreement_ready,reminder',
            'subject' => 'nullable|string|max:255',
            'message' => 'required|string',
            'status' => 'required|boolean',
            'image_url' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->only(['title', 'notification_type', 'subject', 'message', 'status']);
        $data['image'] = $request->input('image_url');
        $data['created_by'] = auth()->id();

        $template = NotificationTemplate::create($data);

        return response()->json([
            'status' => true,
            'message' => 'Template created successfully.',
            'data' => $template
        ], 210);
    }

    /**
     * View template details.
     */
    public function showTemplate($id)
    {
        $template = NotificationTemplate::find($id);

        if (!$template) {
            return response()->json([
                'status' => false,
                'message' => 'Template not found.'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Template details retrieved.',
            'data' => $template
        ]);
    }

    /**
     * Update template.
     */
    public function updateTemplate(Request $request, $id)
    {
        $template = NotificationTemplate::find($id);

        if (!$template) {
            return response()->json([
                'status' => false,
                'message' => 'Template not found.'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'notification_type' => 'required|string|in:new_template,offer,payment_success,payment_failed,agreement_ready,reminder',
            'subject' => 'nullable|string|max:255',
            'message' => 'required|string',
            'status' => 'required|boolean',
            'image_url' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = $request->only(['title', 'notification_type', 'subject', 'message', 'status']);
        if ($request->has('image_url')) {
            $data['image'] = $request->input('image_url');
        }

        $template->update($data);

        return response()->json([
            'status' => true,
            'message' => 'Template updated successfully.',
            'data' => $template
        ]);
    }

    /**
     * Delete template.
     */
    public function destroyTemplate($id)
    {
        $template = NotificationTemplate::find($id);

        if (!$template) {
            return response()->json([
                'status' => false,
                'message' => 'Template not found.'
            ], 404);
        }

        $template->delete();

        return response()->json([
            'status' => true,
            'message' => 'Template deleted successfully.'
        ]);
    }

    // --- NOTIFICATION SENDING ENDPOINT ---

    /**
     * Send push notification using filters.
     */
    public function sendNotification(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'notification_type' => 'required|string|in:new_template,offer,payment_success,payment_failed,agreement_ready,reminder',
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'image_url' => 'nullable|string',
            'schedule_type' => 'required|string|in:immediate,schedule',
            'scheduled_date_time' => 'required_if:schedule_type,schedule|nullable|date|after:now',
            'audience_type' => 'required|string|in:all,category,state,city,new_users'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $filters = $request->all();
        $query = $this->notificationService->getFilteredCustomersQuery($filters);
        $totalRecipients = $query->count();

        if ($totalRecipients === 0) {
            return response()->json([
                'status' => false,
                'message' => 'No recipients match the selected filter configuration.'
            ], 400);
        }

        $recipients = $query->select(['id', 'fcm_token'])->get();

        DB::beginTransaction();
        try {
            $scheduledAt = null;
            $status = 'pending';

            if ($request->input('schedule_type') === 'schedule') {
                $scheduledAt = Carbon::parse($request->input('scheduled_date_time'));
                $status = 'scheduled';
            }

            $history = NotificationHistory::create([
                'title' => $request->input('title'),
                'message' => $request->input('message'),
                'image' => $request->input('image_url'),
                'notification_type' => $request->input('notification_type'),
                'total_recipients' => $totalRecipients,
                'success_count' => 0,
                'failed_count' => 0,
                'sent_by' => auth()->id(),
                'scheduled_at' => $scheduledAt,
                'status' => $status
            ]);

            $recipientRecords = [];
            foreach ($recipients as $recipient) {
                $recipientRecords[] = [
                    'notification_history_id' => $history->id,
                    'customer_id' => $recipient->id,
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

            if ($status === 'pending') {
                $history->update(['status' => 'sending']);
                \App\Jobs\SendPushNotificationJob::dispatch($history->id);
                return response()->json([
                    'status' => true,
                    'message' => 'Bulk push notification processing started in the background.',
                    'history_id' => $history->id
                ]);
            }

            return response()->json([
                'status' => true,
                'message' => 'Push notification successfully scheduled for ' . $scheduledAt->toDateTimeString() . '.',
                'history_id' => $history->id
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Failed to process notification request: ' . $e->getMessage()
            ], 500);
        }
    }

    // --- CAMPAIGN HISTORY ENDPOINTS ---

    /**
     * Get campaign history list.
     */
    public function listHistory(Request $request)
    {
        $type = $request->input('notification_type');
        $status = $request->input('status');

        $query = NotificationHistory::with('sender');

        if (!empty($type)) {
            $query->where('notification_type', $type);
        }

        if (!empty($status)) {
            $query->where('status', $status);
        }

        $history = $query->orderBy('created_at', 'desc')->paginate(15);

        return response()->json([
            'status' => true,
            'message' => 'History logs retrieved.',
            'data' => $history
        ]);
    }

    /**
     * Get details of a campaign including recipient log.
     */
    public function showHistory($id)
    {
        $log = NotificationHistory::with('sender')->find($id);

        if (!$log) {
            return response()->json([
                'status' => false,
                'message' => 'Campaign history log not found.'
            ], 404);
        }

        $recipients = NotificationHistoryUser::with('customer')
            ->where('notification_history_id', $id)
            ->paginate(30);

        return response()->json([
            'status' => true,
            'message' => 'Campaign history and recipient details retrieved.',
            'data' => [
                'campaign' => $log,
                'recipients' => $recipients
            ]
        ]);
    }
}
