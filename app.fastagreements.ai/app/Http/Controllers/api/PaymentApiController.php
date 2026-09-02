<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\CustomerSubscription;
use App\Models\PaymentOrder;
use App\Models\SubscriptionInvoice;
use App\Models\SubscriptionPlan;
use App\Services\Payment\PaymentException;
use App\Services\Payment\PaymentFulfilmentService;
use App\Services\Payment\PaymentGatewayException;
use App\Services\Payment\PaymentOrderService;
use App\Services\Payment\PaymentVerificationService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentApiController extends Controller
{
    public function __construct(
        private readonly PaymentOrderService $orders,
        private readonly PaymentVerificationService $verification,
        private readonly PaymentFulfilmentService $fulfilment,
    ) {
    }

    public function createOrder(Request $request): JsonResponse
    {
        $data = $request->validate([
            'purpose' => 'nullable|in:agreement',
            'subscription_plan_id' => 'required_without:purpose|integer',
            'otp_mode' => 'required_if:purpose,agreement|nullable|in:'
                . SubscriptionPlan::OTP_WITH . ',' . SubscriptionPlan::OTP_WITHOUT,
        ]);

        $customerId = (int) $request->user()->id;
        $otpMode = $data['otp_mode'] ?? null;

        try {
            // For an agreement purchase the client states intent, not a plan —
            // it must not have to know which tier maps to which plan id.
            if (($data['purpose'] ?? null) === 'agreement') {
                // required_if guarantees this, but assert rather than let a
                // null coerce to "" and silently resolve the wrong plan.
                if ($otpMode === null) {
                    return ApiResponse::error(422, 'PLAN_UNAVAILABLE', 'An agreement purchase must name its OTP tier.');
                }

                $planId = $this->orders->resolveAgreementPlan($otpMode)->id;
            } else {
                $planId = (int) $data['subscription_plan_id'];
            }

            $result = $this->orders->createFor($customerId, $planId, $otpMode);
        } catch (PaymentException $e) {
            return ApiResponse::error(422, 'PLAN_UNAVAILABLE', $e->getMessage());
        } catch (PaymentGatewayException $e) {
            report($e);

            return ApiResponse::error(502, 'GATEWAY_UNAVAILABLE', 'The payment gateway is unavailable. Please try again.');
        }

        if (!$result['payment_required']) {
            return ApiResponse::ok([
                'payment_required' => false,
            ], 'Customer already has entitlement.');
        }

        $order = $result['order'];

        return ApiResponse::ok([
            'payment_required' => true,
            'razorpay_order_id' => $order->razorpay_order_id,
            'amount' => $order->amount_paise,
            'currency' => $order->currency,
            'key_id' => $result['key_id'],
        ]);
    }

    public function verify(Request $request): JsonResponse
    {
        $data = $request->validate([
            'razorpay_order_id' => 'required|string',
            'razorpay_payment_id' => 'required|string',
            'razorpay_signature' => 'required|string',
        ]);

        $customerId = (int) $request->user()->id;

        $order = PaymentOrder::query()
            ->where('customer_id', $customerId)
            ->where('razorpay_order_id', $data['razorpay_order_id'])
            ->first();

        if ($order === null) {
            return ApiResponse::error(404, 'ORDER_NOT_FOUND', 'Order not found.');
        }

        $valid = $this->verification->checkoutSignatureValid(
            $data['razorpay_order_id'],
            $data['razorpay_payment_id'],
            $data['razorpay_signature'],
        );

        if (!$valid) {
            return ApiResponse::error(422, 'SIGNATURE_INVALID', 'Invalid signature.');
        }

        $order = $this->fulfilment->fulfil($order, $data['razorpay_payment_id'], $data['razorpay_signature']);

        $invoice = SubscriptionInvoice::query()->where('payment_order_id', $order->id)->first();
        $subscriptionId = CustomerSubscription::query()->where('payment_order_id', $order->id)->value('id');

        return ApiResponse::ok([
            'subscription_id' => $subscriptionId,
            'invoice_id' => $invoice?->id,
        ]);
    }
}
