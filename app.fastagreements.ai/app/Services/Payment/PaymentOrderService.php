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
