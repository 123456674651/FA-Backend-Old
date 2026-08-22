<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\LegalNoticeNotification;
use App\Http\Resources\LegalNoticeNotificationResource;
use Illuminate\Http\Request;
use Exception;

class NotificationController extends Controller
{
    /**
     * Display a listing of the notifications for the authenticated user.
     */
    public function index(Request $request)
    {
        try {
            $userId = auth()->id() ?? $request->input('user_id');

            if (!$userId) {
                return response()->json([
                    'status' => false,
                    'message' => 'User ID is required.'
                ], 400);
            }

            $notifications = LegalNoticeNotification::where('user_id', $userId)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'status' => true,
                'message' => 'Notifications fetched successfully.',
                'data' => LegalNoticeNotificationResource::collection($notifications)
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to fetch notifications.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
