<?php

namespace App\Services;

use App\Models\CustomerSubscription;
use App\Models\SubscriptionPlan;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Whether a customer's plan pays for the agreement they are creating.
 *
 * The legacy flow never asked: `create_aggriment` accepted an `invoice_id`
 * straight from the request body and created the agreement regardless, so a
 * customer with no subscription at all could create unlimited agreements.
 * This is the check that was missing.
 *
 * Ported from the Node service's agreementEntitlementService.
 */
class AgreementEntitlementService
{
    /**
     * The subscription that would cover a new agreement, or null.
     *
     * Deliberately does not reuse Customer::subscriptionStatus(): that method
     * reports an expired date-bounded plan in a form fine for a status banner
     * but which would hand free agreements to a lapsed subscriber here.
     *
     * @param string|null $otpMode The tier of the agreement being created.
     *                             Null skips the coverage check.
     * @return array{subscription_id: int, remaining_agreements: int|null}|null
     */
    public function getEntitlement(int $customerId, ?string $otpMode = null): ?array
    {
        $today = Carbon::today()->toDateString();

        $subscription = CustomerSubscription::query()
            ->where('customer_id', $customerId)
            ->where('is_active', 1)
            // A null end_date is lifetime or quota-limited; both stay valid on date.
            ->where(function ($query) use ($today) {
                $query->whereNull('end_date')->orWhereDate('end_date', '>=', $today);
            })
            // A without_otp plan is the cheaper tier, so it cannot pay for an
            // OTP-verified agreement. The reverse is fine: a with_otp holder
            // overpaid, and NULL predates the feature and covers both.
            ->when($otpMode === SubscriptionPlan::OTP_WITH, function ($query) {
                $query->where(function ($q) {
                    $q->whereNull('otp_mode')->orWhere('otp_mode', SubscriptionPlan::OTP_WITH);
                });
            })
            ->orderByDesc('id')
            ->first();

        if ($subscription === null) {
            return null;
        }

        // A quota plan is spent once the counter reaches zero. Null is unlimited.
        if ($subscription->remaining_agreements !== null && (int) $subscription->remaining_agreements <= 0) {
            return null;
        }

        return [
            'subscription_id' => (int) $subscription->id,
            'remaining_agreements' => $subscription->remaining_agreements === null
                ? null
                : (int) $subscription->remaining_agreements,
        ];
    }

    /**
     * Draws one agreement down from a quota plan. A no-op for unlimited plans.
     *
     * Must be called inside the caller's transaction. Re-reads the counter
     * under a row lock, so two agreements racing on the last remaining credit
     * cannot both succeed — without the lock, both would read `1`, both would
     * write `0`, and the customer gets a free agreement.
     *
     * Returns false when the plan turned out to be exhausted or deactivated
     * between getEntitlement() and here, which the caller must treat as
     * "not covered after all".
     */
    public function consume(int $subscriptionId): bool
    {
        $subscription = CustomerSubscription::query()
            ->whereKey($subscriptionId)
            ->lockForUpdate()
            ->first();

        if ($subscription === null || !$subscription->is_active) {
            return false;
        }

        if ($subscription->remaining_agreements === null) {
            return true;
        }

        $remaining = (int) $subscription->remaining_agreements;

        if ($remaining <= 0) {
            return false;
        }

        // Expressed as a relative decrement rather than a computed absolute so
        // the write cannot clobber a concurrent one, belt-and-braces with the lock.
        CustomerSubscription::query()
            ->whereKey($subscriptionId)
            ->update(['remaining_agreements' => DB::raw('remaining_agreements - 1')]);

        return true;
    }
}
