<?php

namespace App\Services\Payment;

use App\Models\PaymentOrder;
use App\Models\SubscriptionPlan;
use App\Services\AgreementEntitlementService;
use Illuminate\Support\Facades\DB;

class PaymentOrderService
{
    public function __construct(
        private readonly AgreementEntitlementService $entitlements,
        private readonly RazorpayGateway $gateway,
    ) {
    }

    /**
     * The plan that sells a single agreement at the requested tier.
     *
     * An exact tier match wins; a NULL-tier plan is the fallback because NULL
     * means "covers both". Resolving server-side keeps plan ids out of the app,
     * so repricing or re-tiering never needs a client release.
     *
     * @throws PaymentException
     */
    public function resolveAgreementPlan(string $otpMode): SubscriptionPlan
    {
        $plan = SubscriptionPlan::query()
            ->where('duration_type', 'per_agreement')
            ->where('is_active', 1)
            ->where(function ($q) use ($otpMode) {
                $q->where('otp_mode', $otpMode)->orWhereNull('otp_mode');
            })
            // Exact tier first, NULL fallback second.
            ->orderByRaw('otp_mode IS NULL')
            ->orderByDesc('id')
            ->first();

        if ($plan === null) {
            throw new PaymentException('No pay-per-agreement plan is available for that verification tier.');
        }

        return $plan;
    }

    /**
     * @return array{payment_required: bool, order?: PaymentOrder, key_id?: string}
     */
    public function createFor(int $customerId, int $planId, ?string $otpMode): array
    {
        $plan = SubscriptionPlan::query()->find($planId);

        if ($plan === null || !$plan->is_active) {
            throw new PaymentException('Unknown or inactive subscription plan.');
        }

        if ($plan->isPerAgreement() && $otpMode === null) {
            throw new PaymentException('Per-agreement plans require an otp_mode.');
        }

        // A without_otp plan cannot deliver with_otp coverage. Caught here,
        // before any money moves — otherwise the customer is charged, granted
        // the plan's actual (lower) tier, and then refused at agreement
        // creation with no refund path.
        if ($otpMode === SubscriptionPlan::OTP_WITH && $plan->otp_mode === SubscriptionPlan::OTP_WITHOUT) {
            throw new PaymentException('This plan does not cover OTP-verified agreements.');
        }

        // Only agreement purchases are checked against existing cover. Running
        // this for a subscription would refuse to sell a plan to anyone who
        // already has one, blocking every renewal and upgrade.
        if ($plan->isPerAgreement() && $this->entitlements->getEntitlement($customerId, $otpMode) !== null) {
            return ['payment_required' => false];
        }

        $order = DB::transaction(function () use ($customerId, $plan, $otpMode) {
            $local = PaymentOrder::create([
                'customer_id' => $customerId,
                'subscription_plan_id' => $plan->id,
                'status' => PaymentOrder::STATUS_CREATED,
                'amount_paise' => $plan->amountPaise(),
                'currency' => 'INR',
                'expires_at' => now()->addMinutes(15),
            ]);

            $gatewayOrderId = $this->gateway->createOrder(
                $local->amount_paise,
                $local->currency,
                (string) $local->id,
                [
                    'customer_id' => (string) $customerId,
                    'plan_id' => (string) $plan->id,
                    'otp_mode' => $otpMode ?? 'null',
                ],
            );

            $local->razorpay_order_id = $gatewayOrderId;
            $local->save();

            return $local;
        });

        return [
            'payment_required' => true,
            'order' => $order,
            'key_id' => (string) config('services.razorpay.key_id'),
        ];
    }
}
