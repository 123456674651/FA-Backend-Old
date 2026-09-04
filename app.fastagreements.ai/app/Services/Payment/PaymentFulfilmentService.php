<?php

namespace App\Services\Payment;

use App\Models\CustomerSubscription;
use App\Models\PaymentOrder;
use App\Models\SubscriptionInvoice;
use App\Models\SubscriptionPlan;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Grants what a verified payment bought.
 *
 * Reached by two independent routes — the client's success callback and
 * Razorpay's webhook — which will both fire for the same payment as a matter
 * of course. Everything here is therefore idempotent under a row lock: the
 * second caller must find the work done and change nothing.
 */
class PaymentFulfilmentService
{
    public function fulfil(PaymentOrder $order, string $razorpayPaymentId, ?string $signature = null): PaymentOrder
    {
        return DB::transaction(function () use ($order, $razorpayPaymentId, $signature) {
            $locked = PaymentOrder::whereKey($order->getKey())->lockForUpdate()->first();

            // The customer row can be deleted out from under an in-flight
            // order (payment_orders.customer_id cascades on delete); without
            // this guard a late webhook would fatal into a 500 and Razorpay
            // would retry forever.
            if ($locked === null) {
                return $order;
            }

            // Not an error: the callback and the webhook race by design.
            if ($locked->status === PaymentOrder::STATUS_PAID) {
                return $locked;
            }

            $plan = SubscriptionPlan::findOrFail($locked->subscription_plan_id);

            $this->deactivateSupersededSubscriptions($locked->customer_id, $plan);

            $start = Carbon::today();

            $subscription = CustomerSubscription::create([
                'customer_id' => $locked->customer_id,
                'subscription_plan_id' => $plan->id,
                // Snapshotted, not read live: a later edit to the plan must not
                // change the coverage this customer paid for.
                'otp_mode' => $plan->otp_mode,
                'payment_order_id' => $locked->id,
                'start_date' => $start,
                'end_date' => $plan->resolveEndDate($start),
                'remaining_agreements' => $plan->isPerAgreement() ? ($plan->agreement_limit ?? 1) : null,
                'is_active' => 1,
            ]);

            SubscriptionInvoice::create([
                'customer_id' => $locked->customer_id,
                'subscription_plan_id' => $plan->id,
                'customer_subscription_id' => $subscription->id,
                'payment_order_id' => $locked->id,
                'invoice_number' => $this->invoiceNumber(),
                // A string, never a float: money is integer paise, and a
                // float division here would touch money against this
                // branch's own constraint.
                'amount' => number_format($locked->amount_paise / 100, 2, '.', ''),
                'invoice_date' => Carbon::today(),
                'payment_status' => 'paid',
                'payment_method' => 'online',
            ]);

            $locked->forceFill([
                'status' => PaymentOrder::STATUS_PAID,
                'razorpay_payment_id' => $razorpayPaymentId,
                'razorpay_signature' => $signature,
                'fulfilled_at' => now(),
            ])->save();

            return $locked;
        });
    }

    /**
     * Records a payment that did not succeed. Never downgrades a paid order —
     * a late failure webhook after a captured payment must not revoke it.
     */
    public function markFailed(PaymentOrder $order, string $reason): PaymentOrder
    {
        return DB::transaction(function () use ($order, $reason) {
            $locked = PaymentOrder::whereKey($order->getKey())->lockForUpdate()->first();

            if ($locked === null) {
                return $order;
            }

            if ($locked->status === PaymentOrder::STATUS_PAID) {
                return $locked;
            }

            $locked->forceFill([
                'status' => PaymentOrder::STATUS_FAILED,
                'failure_reason' => Str::limit($reason, 250),
            ])->save();

            return $locked;
        });
    }

    /**
     * Deactivates only subscriptions of the same kind.
     *
     * renew() deactivated every prior row, which destroyed value in both
     * directions: buying a plan wiped an unused per-agreement credit, and
     * buying a credit switched off a running plan. A new time-based plan
     * replaces the running one; a credit supersedes nothing and simply stacks.
     */
    private function deactivateSupersededSubscriptions(int $customerId, SubscriptionPlan $plan): void
    {
        if ($plan->isPerAgreement()) {
            return;
        }

        // Identified by the plan's duration_type, not by a null
        // remaining_agreements: that column defaults to 0 in this schema, so
        // legacy time-based rows carry 0 rather than NULL and a null check
        // would silently skip them.
        $timeBasedPlanIds = SubscriptionPlan::query()
            ->where('duration_type', '!=', 'per_agreement')
            ->pluck('id');

        $supersededIds = CustomerSubscription::query()
            ->where('customer_id', $customerId)
            ->where('is_active', 1)
            ->whereIn('subscription_plan_id', $timeBasedPlanIds)
            // Skip rows the incoming plan cannot cover: a without_otp
            // purchase must not silently deactivate a with_otp subscription
            // the customer already paid for and is still within its dates.
            ->when($plan->otp_mode === SubscriptionPlan::OTP_WITHOUT, function ($query) {
                $query->where(function ($q) {
                    $q->whereNull('otp_mode')->orWhere('otp_mode', SubscriptionPlan::OTP_WITHOUT);
                });
            })
            ->pluck('id');

        if ($supersededIds->isNotEmpty()) {
            // Two statements rather than an update with whereHas: MariaDB
            // cannot update a table that a subquery in the same statement reads.
            CustomerSubscription::whereIn('id', $supersededIds)->update(['is_active' => 0]);
        }
    }

    private function invoiceNumber(): string
    {
        return 'INV-' . now()->format('YmdHis') . '-' . strtoupper(Str::random(6));
    }
}
