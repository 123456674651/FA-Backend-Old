<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\PaymentOrder;
use App\Services\Payment\PaymentFulfilmentService;
use App\Services\Payment\PaymentVerificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Razorpay's own report of what happened, independent of the client.
 *
 * This is what makes the flow survive the app being killed after payment:
 * without it, a customer who closes the app mid-checkout is charged and
 * receives nothing.
 *
 * Unauthenticated by necessity — Razorpay holds no token — and authenticated
 * instead by an HMAC over the raw body.
 */
class RazorpayWebhookController extends Controller
{
    public function __construct(
        private readonly PaymentVerificationService $verifier,
        private readonly PaymentFulfilmentService $fulfilment,
    ) {
    }

    public function handle(Request $request): JsonResponse
    {
        // getContent(), never $request->all(): the signature covers the exact
        // bytes Razorpay sent, and a re-encoded array will not verify.
        $rawBody = $request->getContent();
        $signature = (string) $request->header('X-Razorpay-Signature', '');

        if (!$this->verifier->webhookSignatureValid($rawBody, $signature)) {
            Log::warning('Rejected a Razorpay webhook with an invalid signature.');

            return response()->json(['status' => false], 401);
        }

        $payload = json_decode($rawBody, true) ?: [];
        $event = (string) ($payload['event'] ?? '');
        $entity = $payload['payload']['payment']['entity'] ?? [];

        $razorpayOrderId = (string) ($entity['order_id'] ?? '');
        $razorpayPaymentId = (string) ($entity['id'] ?? '');

        $order = $razorpayOrderId === ''
            ? null
            : PaymentOrder::where('razorpay_order_id', $razorpayOrderId)->first();

        // Acknowledge anything we cannot act on. A non-2xx makes Razorpay retry,
        // and retrying an event about an order we have never heard of only
        // produces noise.
        if ($order === null) {
            return response()->json(['status' => true, 'message' => 'ignored']);
        }

        match ($event) {
            'payment.captured' => $this->fulfilment->fulfil($order, $razorpayPaymentId),
            // Razorpay puts the failure reason directly on the payment entity
            // (payload.payment.entity.error_description), not nested under an
            // "error" object.
            'payment.failed' => $this->fulfilment->markFailed(
                $order,
                (string) ($entity['error_description'] ?? 'Payment failed at the gateway.'),
            ),
            default => null,
        };

        return response()->json(['status' => true]);
    }
}
