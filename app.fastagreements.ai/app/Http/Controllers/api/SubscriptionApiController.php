<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use App\Models\CustomerSubscription;
use Illuminate\Http\Request;
use App\Models\SubscriptionPlan;
use App\Models\SubscriptionInvoice;
use Illuminate\Support\Str;
use App\Models\DealCategory;

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
  

public function renew(Request $request)
{
    $request->validate([
        'customer_id' => 'required|integer',
        'subscription_plan_id' => 'required|integer',

        'agreement_category_id' => 'nullable|integer',
        'agreement_sub_category_id' => 'nullable|integer',
    ]);

    $plan = SubscriptionPlan::findOrFail($request->subscription_plan_id);

    // Fetch category names (optional but recommended)
    $category = DealCategory::find($request->agreement_category_id);
    $subCategory = DealCategory::find($request->agreement_sub_category_id);

    CustomerSubscription::where('customer_id', $request->customer_id)
        ->update(['is_active' => 0]);

    $startDate = Carbon::today();
    $endDate = null;
    $remainingAgreements = null;

    switch ($plan->duration_type) {
        case 'per_agreement':
            $remainingAgreements = $plan->agreement_limit ?? 1;
            break;
        case 'days':
            $endDate = $startDate->copy()->addDays($plan->duration_value);
            break;
        case 'months':
            $endDate = $startDate->copy()->addMonths($plan->duration_value);
            break;
        case 'yearly':
            $endDate = $startDate->copy()->addYears($plan->duration_value);
            break;
        case 'lifetime':
            $endDate = null;
            break;
    }

    $subscription = CustomerSubscription::create([
        'customer_id' => $request->customer_id,
        'subscription_plan_id' => $plan->id,
        'start_date' => $startDate,
        'end_date' => $endDate,
        'remaining_agreements' => $remainingAgreements,
        'is_active' => 1,
    ]);

  $invoiceNumber = 'INV-' . now()->format('YmdHis') . '-' . strtoupper(Str::random(4));

    // 🔥 Create Invoice with Category
    $invoice = SubscriptionInvoice::create([
        'customer_id' => $request->customer_id,
        'subscription_plan_id' => $plan->id,
        'customer_subscription_id' => $subscription->id,

        'agreement_category_id' => $category?->id,
        'agreement_sub_category_id' => $subCategory?->id,
        'agreement_category_name' => $category?->category_name,
        'agreement_sub_category_name' => $subCategory?->category_name,
        'invoice_number' => $invoiceNumber,
        'amount' => $plan->price,
        'invoice_date' => Carbon::today(),
        'payment_status' => 'paid',
        'payment_method' => 'online',
    ]);

    return response()->json([
        'status' => 'success',
        'message' => 'Subscription activated & invoice generated',
        'invoice' => $invoice
    ]);
}
    public function renewx(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|integer',
            'subscription_plan_id' => 'required|integer',
        ]);

        $plan = SubscriptionPlan::findOrFail($request->subscription_plan_id);

        // deactivate old subscriptions (except per agreement if you want stacking)
        CustomerSubscription::where('customer_id', $request->customer_id)
            ->update(['is_active' => 0]);

        $startDate = Carbon::today();
        $endDate = null;
        $remainingAgreements = null;

        switch ($plan->duration_type) {

            case 'per_agreement':
                $remainingAgreements = $plan->agreement_limit ?? 1;
                break;

            case 'days':
                $endDate = $startDate->copy()->addDays($plan->duration_value);
                break;

            case 'months':
                $endDate = $startDate->copy()->addMonths($plan->duration_value);
                break;

            case 'years':
                $endDate = $startDate->copy()->addYears($plan->duration_value);
                break;

            case 'lifetime':
                $endDate = null;
                break;
        }

        CustomerSubscription::create([
            'customer_id' => $request->customer_id,
            'subscription_plan_id' => $plan->id,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'remaining_agreements' => $remainingAgreements,
            'is_active' => 1,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Subscription activated successfully',
            'remaining_agreements' => $remainingAgreements,
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
