<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\PaymentOrder;
use App\Models\SubscriptionInvoice;
use App\Services\Payment\PaymentException;
use App\Services\Payment\PaymentFulfilmentService;
use App\Services\Payment\PaymentOrderService;
use App\Services\Payment\PaymentVerificationService;
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
            'subscription_plan_id' => 'required|integer',
            'otp_mode' => 'nullable|string',
        ]);

        $customerId = (int) $request->user()->id;

        try {
            $result = $this->orders->createFor($customerId, (int) $data['subscription_plan_id'], $data['otp_mode'] ?? null);
        } catch (PaymentException $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ], 422);
        }

        if (!$result['payment_required']) {
            return response()->json([
                'status' => 'success',
                'payment_required' => false,
                'message' => 'Customer already has entitlement.',
            ]);
        }

        $order = $result['order'];

        return response()->json([
            'status' => 'success',
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
            return response()->json(['status' => false, 'message' => 'Order not found.'], 404);
        }

        $valid = $this->verification->checkoutSignatureValid(
            $data['razorpay_order_id'],
            $data['razorpay_payment_id'],
            $data['razorpay_signature'],
        );

        if (!$valid) {
            return response()->json(['status' => false, 'message' => 'Invalid signature.'], 422);
        }

        $order = $this->fulfilment->fulfil($order, $data['razorpay_payment_id'], $data['razorpay_signature']);

        $invoice = SubscriptionInvoice::query()->where('payment_order_id', $order->id)->first();

        return response()->json([
            'status' => 'success',
            'subscription_id' => $order->subscription_plan_id,
            'invoice_id' => $invoice?->id,
        ]);
    }
}
