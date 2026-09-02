<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use App\Models\CustomerSubscription;
use App\Models\SubscriptionPlan;

class SubscriptionApiController extends Controller
{
    public function status($customer_id)
    {
        $today = Carbon::today();



        $subscription = CustomerSubscription::where('customer_id', $customer_id)
            ->where('is_active', 1)
            ->orderBy('end_date', 'desc')
            ->first();

        if ($subscription->remaining_agreements !== null) {

            if ($subscription->remaining_agreements <= 0) {
                return response()->json([
                    'status' => 'expired',
                    'message' => 'Agreement limit exhausted',
                    'remaining_agreements' => 0,
                ]);
            }

            return response()->json([
                'status' => 'active',
                'message' => 'Per agreement plan active',
                'remaining_agreements' => $subscription->remaining_agreements,
            ]);
        }


        if (!$subscription) {
            return response()->json([
                'status' => 'inactive',
                'message' => 'No active subscription found'
            ]);
        }

        $start = Carbon::parse($subscription->start_date);
        $end   = Carbon::parse($subscription->end_date);

        // Lifetime plan
        if (is_null($end)) {
            return response()->json([
                'status' => 'active',
                'days_remaining' => null,
                'message' => 'Lifetime subscription active',
                'start_date' => $start->toDateString(),
                'end_date' => null,
                'is_expiring_soon' => false,
            ]);
        }

        // Expired
        if ($today->gt($end)) {
            return response()->json([
                'status' => 'expired',
                'days_remaining' => 0,
                'message' => 'Your subscription has expired',
                'start_date' => $start->toDateString(),
                'end_date' => $end->toDateString(),
                'is_expiring_soon' => false
            ]);
        }

        // Not started yet
        if ($today->lt($start)) {
            return response()->json([
                'status' => 'inactive',
                'days_remaining' => $start->diffInDays($today),
                'message' => 'Subscription not started yet',
                'start_date' => $start->toDateString(),
                'end_date' => $end->toDateString(),
                'is_expiring_soon' => false
            ]);
        }

        // Active
        $daysRemaining = $today->diffInDays($end);
        $isExpiringSoon = $daysRemaining <= 7;

        return response()->json([
            'status' => 'active',
            'days_remaining' => $daysRemaining,
            'message' => $isExpiringSoon
                ? "Your subscription will expire in {$daysRemaining} days"
                : 'Your subscription is active',
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
            'is_expiring_soon' => $isExpiringSoon
        ]);
    }


    public function subscription_plane_list()
    {
        $plans = SubscriptionPlan::where('is_active', 1)
            ->orderBy('price', 'asc')
            ->get([
                'id',
                'name',
                'duration_type',
                'price',
                'validity_days',
                'agreement_limit'
            ]);

        return response()->json([
            'status' => true,
            'message' => 'Subscription plans fetched successfully',
            'data' => $plans
        ], 200);
    }
}
