<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NotificationTemplate;
use App\Models\NotificationHistory;
use App\Models\NotificationHistoryUser;
use App\Models\DealCategory;
use App\Models\State;
use App\Models\City;
use App\Models\Language;
use App\Models\Customer;
use App\Services\PushNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SendNotificationController extends Controller
{
    protected $notificationService;

    public function __construct(PushNotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Display the send notification form.
     */
    public function index()
    {
        $templates = NotificationTemplate::where('status', true)->orderBy('title')->get();
        $categories = DealCategory::orderBy('category_name')->get();
        $states = State::orderBy('name')->get();
        $cities = City::orderBy('city')->get();
        $languages = Language::orderBy('language_name')->get();
        $customers = Customer::orderBy('name')->get();

        $types = [
            'new_template' => 'New Template',
            'offer' => 'Offer',
            'payment_success' => 'Payment Success',
            'payment_failed' => 'Payment Failed',
            'agreement_ready' => 'Agreement Ready',
            'reminder' => 'Reminder'
        ];

        return view('admin.notifications.send', compact(
            'templates', 'categories', 'states', 'cities', 'languages', 'customers', 'types'
        ));
    }

    /**
     * AJAX route for matching recipients preview.
     */
    public function preview(Request $request)
    {
        $filters = $request->all();
        $query = $this->notificationService->getFilteredCustomersQuery($filters);
        
        $totalCount = $query->count();
        // Fetch first 5 users with details for visual preview
        $previewUsers = $query->limit(5)->get(['id', 'name', 'mobile', 'email']);

        return response()->json([
            'status' => true,
            'total_recipients' => $totalCount,
            'preview' => $previewUsers
        ]);
    }

    /**
     * Send or schedule the notification.
     */
    public function send(Request $request)
    {
        $request->validate([
            'notification_type' => 'required|string|in:new_template,offer,payment_success,payment_failed,agreement_ready,reminder',
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'image_file' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'schedule_type' => 'required|string|in:immediate,schedule',
            'scheduled_date_time' => 'required_if:schedule_type,schedule|nullable|date|after:now',
            'audience_type' => 'required|string|in:all,category,state,city,new_users'
        ]);

        $filters = $request->all();
        $query = $this->notificationService->getFilteredCustomersQuery($filters);
        $totalRecipients = $query->count();

        if ($totalRecipients === 0) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'No recipients match the selected filter configuration.');
        }

        // Retrieve FCM tokens for recipients
        // We select the customer ID and fcm_token
        $recipients = $query->select(['id', 'fcm_token'])->get();

        // Handle optional override image upload
        $imagePath = null;
        if ($request->hasFile('image_file')) {
            $file = $request->file('image_file');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/notifications'), $filename);
            $imagePath = 'uploads/notifications/' . $filename;
        } elseif (!empty($request->input('template_image'))) {
            $imagePath = $request->input('template_image'); // Keep template's original image
        }

        // Start Database Transaction for integrity
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
                'image' => $imagePath,
                'notification_type' => $request->input('notification_type'),
                'total_recipients' => $totalRecipients,
                'success_count' => 0,
                'failed_count' => 0,
                'sent_by' => auth()->id(),
                'scheduled_at' => $scheduledAt,
                'status' => $status
            ]);

            // Save recipients list in database
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

            // Insert in chunks of 500 records to bypass query parameter limits
            foreach (array_chunk($recipientRecords, 500) as $chunk) {
                NotificationHistoryUser::insert($chunk);
            }

            DB::commit();

            // Dispatch background job immediately if immediate send
            if ($status === 'pending') {
                $history->update(['status' => 'sending']);
                \App\Jobs\SendPushNotificationJob::dispatch($history->id);
                return redirect()->route('notification-history.index')
                    ->with('success', 'Bulk push notification processing started in the background.');
            }

            return redirect()->route('notification-history.index')
                ->with('success', 'Push notification successfully scheduled for ' . $scheduledAt->toDateTimeString() . '.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to submit notification request: ' . $e->getMessage());
        }
    }
}
