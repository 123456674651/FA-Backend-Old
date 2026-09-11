<?php

namespace App\Http\Controllers\Api\V2;

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
use App\Services\Payment\RazorpayApiGateway;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Same flow as Api\PaymentApiController, but the caller names which Razorpay
 * key pair to use ('test' or 'live') per order instead of the app always
 * using one fixed pair. Which pair an order was created under is stored on
 * the order itself, so verify() checks the matching secret automatically.
 */
class PaymentApiController extends Controller
{
    // public function __construct(
    //     private readonly PaymentOrderService $orders,
    //     private readonly PaymentVerificationService $verification,
    //     private readonly PaymentFulfilmentService $fulfilment,
    // ) {
    // }

    /** Returns the Razorpay key_id for the requested mode, for clients that just need to init checkout. */
    public function razorypayCredentials(Request $request): JsonResponse
    {

        $keyId = (string) config("services.razorpay.key_id");

        if ($keyId === '') {
            return ApiResponse::error(502, 'GATEWAY_UNAVAILABLE', "Razorpay {$data['mode']} key is not configured.");
        }

        return ApiResponse::ok([
            'key_id' => $keyId,
        ]);
    }

    public function createOrder(Request $request): JsonResponse
    {
        $data = $request->validate([
            'mode' => 'required|in:test,live',
            'purpose' => 'nullable|in:agreement',
            'subscription_plan_id' => 'required_without:purpose|integer',
            'otp_mode' => 'required_if:purpose,agreement|nullable|in:'
                . SubscriptionPlan::OTP_WITH . ',' . SubscriptionPlan::OTP_WITHOUT,
        ]);

        $customerId = (int) $request->user()->id;
        $otpMode = $data['otp_mode'] ?? null;
        $mode = $data['mode'];

        $keyId = (string) config("services.razorpay.{$mode}.key_id");
        $keySecret = (string) config("services.razorpay.{$mode}.key_secret");

        if ($keyId === '' || $keySecret === '') {
            return ApiResponse::error(502, 'GATEWAY_UNAVAILABLE', "Razorpay {$mode} keys are not configured.");
        }

        $gateway = new RazorpayApiGateway($keyId, $keySecret);

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

            $result = $this->orders->createFor($customerId, $planId, $otpMode, $mode, $gateway, $keyId);
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
            'mode' => $mode,
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

        $keySecret = (string) config("services.razorpay.{$order->razorpay_mode}.key_secret");

        $valid = $this->verification->checkoutSignatureValid(
            $data['razorpay_order_id'],
            $data['razorpay_payment_id'],
            $data['razorpay_signature'],
            $keySecret,
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
