<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NotificationTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class NotificationTemplateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $type = $request->input('notification_type');

        $query = NotificationTemplate::query();

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%");
            });
        }

        if (!empty($type)) {
            $query->where('notification_type', $type);
        }

        $templates = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        $types = [
            'new_template' => 'New Template',
            'offer' => 'Offer',
            'payment_success' => 'Payment Success',
            'payment_failed' => 'Payment Failed',
            'agreement_ready' => 'Agreement Ready',
            'reminder' => 'Reminder'
        ];

        return view('admin.notifications.templates.index', compact('templates', 'types', 'search', 'type'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $types = [
            'new_template' => 'New Template',
            'offer' => 'Offer',
            'payment_success' => 'Payment Success',
            'payment_failed' => 'Payment Failed',
            'agreement_ready' => 'Agreement Ready',
            'reminder' => 'Reminder'
        ];

        return view('admin.notifications.templates.create', compact('types'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'notification_type' => 'required|string|in:new_template,offer,payment_success,payment_failed,agreement_ready,reminder',
            'subject' => 'nullable|string|max:255',
            'message' => 'required|string',
            'image_file' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'required|boolean'
        ]);

        $data = $request->only(['title', 'notification_type', 'subject', 'message', 'status']);
        $data['created_by'] = auth()->id();

        if ($request->hasFile('image_file')) {
            $file = $request->file('image_file');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/notifications'), $filename);
            $data['image'] = 'uploads/notifications/' . $filename;
        }

        NotificationTemplate::create($data);

        return redirect()->route('notification-templates.index')
            ->with('success', 'Notification Template created successfully.');
    }

    /**
     * Show the details of the template.
     */
    public function show(NotificationTemplate $notificationTemplate)
    {
        return view('admin.notifications.templates.show', [
            'template' => $notificationTemplate
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(NotificationTemplate $notificationTemplate)
    {
        $types = [
            'new_template' => 'New Template',
            'offer' => 'Offer',
            'payment_success' => 'Payment Success',
            'payment_failed' => 'Payment Failed',
            'agreement_ready' => 'Agreement Ready',
            'reminder' => 'Reminder'
        ];

        return view('admin.notifications.templates.edit', [
            'template' => $notificationTemplate,
            'types' => $types
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, NotificationTemplate $notificationTemplate)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'notification_type' => 'required|string|in:new_template,offer,payment_success,payment_failed,agreement_ready,reminder',
            'subject' => 'nullable|string|max:255',
            'message' => 'required|string',
            'image_file' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status' => 'required|boolean'
        ]);

        $data = $request->only(['title', 'notification_type', 'subject', 'message', 'status']);

        if ($request->hasFile('image_file')) {
            // Delete old image if exists
            if (!empty($notificationTemplate->image) && file_exists(public_path($notificationTemplate->image))) {
                @unlink(public_path($notificationTemplate->image));
            }

            $file = $request->file('image_file');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/notifications'), $filename);
            $data['image'] = 'uploads/notifications/' . $filename;
        }

        $notificationTemplate->update($data);

        return redirect()->route('notification-templates.index')
            ->with('success', 'Notification Template updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(NotificationTemplate $notificationTemplate)
    {
        if (!empty($notificationTemplate->image) && file_exists(public_path($notificationTemplate->image))) {
            @unlink(public_path($notificationTemplate->image));
        }

        $notificationTemplate->delete();

        return redirect()->route('notification-templates.index')
            ->with('success', 'Notification Template deleted successfully.');
    }

    /**
     * Toggle the active/inactive status.
     */
    public function toggleStatus(NotificationTemplate $notificationTemplate)
    {
        $notificationTemplate->update([
            'status' => !$notificationTemplate->status
        ]);

        return redirect()->route('notification-templates.index')
            ->with('success', 'Notification status updated successfully.');
    }
}
