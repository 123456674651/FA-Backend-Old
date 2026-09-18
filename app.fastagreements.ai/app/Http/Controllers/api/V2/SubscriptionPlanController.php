<?php

namespace App\Http\Controllers\api\V2;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;

class SubscriptionPlanController extends Controller
{
    /**
     * Get a list of all active subscription plans
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();

            // Check if the user has an active subscription
            $activeSubscription = \App\Models\CustomerSubscription::where('customer_id', $user->id)
                ->where('is_active', 1)
                ->whereDate('end_date', '>=', now())
                ->first();

            // Fetch all active subscription plans
            $plans = SubscriptionPlan::where('is_active', '!=', 0)->get();

            return response()->json([
                'status' => true,
                'message' => 'Subscription plans fetched successfully.',
                'has_active_subscription' => $activeSubscription ? true : false,
                'active_subscription_details' => $activeSubscription,
                'data' => $plans
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to fetch subscription plans.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
