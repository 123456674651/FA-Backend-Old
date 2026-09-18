<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\api\BaseController;
use App\Models\PaymentHistory;
use App\Models\SubscriptionPlan;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PaymentApiController extends BaseController
{
    /**
     * Get Razorpay credentials (Key ID) for Flutter checkout initialization.
     */
    public function razorypayCredentials(Request $request): JsonResponse
    {
        $keyId = (string) config('services.razorpay.key_id');

        if (empty($keyId)) {
            return $this->sendError('Razorpay key is not configured.');
        }

        return $this->sendResponse(['key_id' => $keyId], 'Key ID fetched successfully.');
    }

    /**
     * Create Razorpay Order and record in payment_histories.
     */
    public function createOrder(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'plan_id' => 'nullable|integer',
            'purpose' => 'nullable|string|max:255',
            'agreement_id' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors()->first());
        }

        $user = $request->user();
        if (!$user) {
            return $this->sendUnauthorized('User not authenticated.');
        }

        $keyId = (string) config('services.razorpay.key_id');
        $keySecret = (string) config('services.razorpay.key_secret');

        if (empty($keyId) || empty($keySecret)) {
            return $this->sendError('Payment gateway configuration is missing.');
        }

        $planId = $request->input('plan_id');

        if (empty($planId)) {
            $amount = 15.00;
        } else {
            $plan = SubscriptionPlan::find($planId);
            if (!$plan) {
                return $this->sendError('Invalid plan selected.');
            }
            $amount = (float) $plan->price;
        }

        $amountPaise = (int) round($amount * 100);
        $receipt = 'RCPT_' . time() . '_' . $user->id;
        $agreementId = $request->input('agreement_id');

        try {
            // Call Razorpay Order API directly via HTTP
            $response = Http::withBasicAuth($keyId, $keySecret)
                ->post('https://api.razorpay.com/v1/orders', [
                    'amount' => $amountPaise,
                    'currency' => 'INR',
                    'receipt' => $receipt,
                    'payment_capture' => 1,
                    'notes' => [
                        'user_id' => $user->id,
                        'agreement_id' => $agreementId ?? '',
                        'purpose' => $request->input('purpose', 'Agreement Payment'),
                    ],
                ]);

            if (!$response->successful()) {
                Log::error('Razorpay order creation failed', [
                    'user_id' => $user->id,
                    'response' => $response->json() ?? $response->body(),
                ]);
                return $this->sendError('Failed to create payment order with gateway.');
            }

            $orderData = $response->json();
            $razorpayOrderId = $orderData['id'];

            // Save initial order in DB
            $paymentHistory = PaymentHistory::create([
                'user_id' => $user->id,
                'agreement_id' => $agreementId,
                'gateway' => 'razorpay',
                'razorpay_order_id' => $razorpayOrderId,
                'amount' => $amount,
                'currency' => 'INR',
                'status' => 'created',
                'purpose' => $request->input('purpose', 'Agreement Payment'),
                'contact' => $user->mobile ?? null,
                'email' => $user->email ?? null,
                'gateway_response' => $orderData,
            ]);

            return $this->sendResponse([
                'order_id' => $razorpayOrderId,
                'agreement_id' => $agreementId,
                'amount' => $amount,
                'amount_paise' => $amountPaise,
                'currency' => 'INR',
                'key_id' => $keyId,
                'payment_history_id' => $paymentHistory->id,
            ], 'Order created successfully.');

        } catch (Exception $e) {
            Log::error('Order creation exception: ' . $e->getMessage());
            return $this->sendError('Something went wrong while creating the order.');
        }
    }

    /**
     * Verify Payment after Flutter App checkout, fetch method details & create Razorpay Invoice.
     */
    public function verify(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'razorpay_order_id' => 'required|string',
            'razorpay_payment_id' => 'required|string',
            'razorpay_signature' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors()->first());
        }

        $orderId = $request->input('razorpay_order_id');
        $paymentId = $request->input('razorpay_payment_id');
        $signature = $request->input('razorpay_signature');

        $paymentHistory = PaymentHistory::where('razorpay_order_id', $orderId)->first();

        if (!$paymentHistory) {
            return $this->sendNotFound('Payment order record not found.');
        }

        // In local/debug environment, allow mock verification with 'mock_test_signature'
        $isMockTest = config('app.debug') && $signature === 'mock_test_signature';

        // Verify HMAC-SHA256 Signature
        $keySecret = (string) config('services.razorpay.key_secret');
        $expectedSignature = hash_hmac('sha256', $orderId . '|' . $paymentId, $keySecret);

        if (!$isMockTest && !hash_equals($expectedSignature, $signature)) {
            $paymentHistory->update([
                'status' => 'failed',
                'failure_reason' => 'Invalid payment signature.',
            ]);
            return $this->sendError('Invalid payment signature verification failed.');
        }

        // Fetch complete payment details from Razorpay (method, card/upi details, fee, tax)
        $paymentDetails = $isMockTest ? [
            'method' => 'upi',
            'vpa' => 'test@razorpay',
            'fee' => 0,
            'tax' => 0,
        ] : $this->fetchRazorpayPaymentDetails($paymentId);

        // Mark payment as paid and generate Razorpay Invoice
        $this->processPaymentSuccess($paymentHistory, $paymentId, $signature, $paymentDetails);

        return $this->sendResponse([
            'payment_history_id' => $paymentHistory->id,
            'status' => $paymentHistory->status,
            'payment_method' => $paymentHistory->payment_method,
            'invoice_number' => $paymentHistory->invoice_number,
            'invoice_url' => $paymentHistory->invoice_pdf,
        ], 'Payment verified successfully.');
    }

    /**
     * Handle Razorpay Webhook events (payment.captured, payment.failed).
     */
    public function webhook(Request $request): JsonResponse
    {
        $rawBody = $request->getContent();
        $signature = (string) $request->header('X-Razorpay-Signature', '');
        $webhookSecret = (string) config('services.razorpay.webhook_secret');

        // Verify Webhook Signature if secret configured
        if (!empty($webhookSecret)) {
            $expectedSignature = hash_hmac('sha256', $rawBody, $webhookSecret);
            if (!hash_equals($expectedSignature, $signature)) {
                Log::warning('Razorpay Webhook signature mismatch.');
                return response()->json(['status' => false, 'message' => 'Invalid signature'], 401);
            }
        }

        $payload = json_decode($rawBody, true) ?: [];
        $event = $payload['event'] ?? '';
        $entity = $payload['payload']['payment']['entity'] ?? [];

        $razorpayOrderId = $entity['order_id'] ?? null;
        $razorpayPaymentId = $entity['id'] ?? null;

        if (!$razorpayOrderId) {
            return response()->json(['status' => true, 'message' => 'Ignored (no order_id)']);
        }

        $paymentHistory = PaymentHistory::where('razorpay_order_id', $razorpayOrderId)->first();
        if (!$paymentHistory) {
            return response()->json(['status' => true, 'message' => 'Order not found in DB']);
        }

        if ($event === 'payment.captured') {
            $this->processPaymentSuccess($paymentHistory, $razorpayPaymentId, null, $entity);
        } elseif ($event === 'payment.failed') {
            $failureReason = $entity['error_description'] ?? 'Payment failed at gateway';
            $errorCode = $entity['error_code'] ?? null;
            $paymentHistory->update([
                'status' => 'failed',
                'failure_reason' => $failureReason,
                'error_code' => $errorCode,
                'gateway_response' => $entity,
            ]);
        }

        return response()->json(['status' => true, 'message' => 'Webhook processed successfully']);
    }

    /**
     * Get user payment history list with detailed method breakdown.
     */
    public function history(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return $this->sendUnauthorized('User not authenticated.');
        }

        $histories = PaymentHistory::where('user_id', $user->id)
            ->orderBy('id', 'desc')
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'agreement_id' => $item->agreement_id,
                    'order_id' => $item->razorpay_order_id,
                    'payment_id' => $item->razorpay_payment_id,
                    'amount' => $item->amount,
                    'currency' => $item->currency,
                    'status' => $item->status,
                    'payment_method' => $item->payment_method,
                    'card_network' => $item->card_network,
                    'card_last4' => $item->card_last4,
                    'bank' => $item->bank,
                    'vpa' => $item->vpa,
                    'wallet' => $item->wallet,
                    'purpose' => $item->purpose,
                    'invoice_number' => $item->invoice_number,
                    'invoice_url' => $item->invoice_pdf,
                    'paid_at' => $item->paid_at ? $item->paid_at->format('Y-m-d H:i:s') : null,
                    'created_at' => $item->created_at->format('Y-m-d H:i:s'),
                ];
            });

        return $this->sendResponse($histories, 'Payment history fetched successfully.');
    }

    /**
     * Get user payment history list with pagination for Settings Section.
     */
    public function paymentHistoriesList(Request $request): JsonResponse
    {
        $user = $request->user();
        if (!$user) {
            return $this->sendUnauthorized('User not authenticated.');
        }

        $perPage = $request->input('per_page', 10);

        $histories = PaymentHistory::where('user_id', $user->id)
            ->orderBy('id', 'desc')
            ->paginate($perPage);

        $histories->getCollection()->transform(function ($item) {
            return [
                'id' => $item->id,
                'agreement_id' => $item->agreement_id,
                'order_id' => $item->razorpay_order_id,
                'payment_id' => $item->razorpay_payment_id,
                'amount' => $item->amount,
                'currency' => $item->currency,
                'status' => $item->status,
                'payment_method' => $item->payment_method,
                'card_network' => $item->card_network,
                'card_last4' => $item->card_last4,
                'bank' => $item->bank,
                'vpa' => $item->vpa,
                'wallet' => $item->wallet,
                'purpose' => $item->purpose,
                'invoice_number' => $item->invoice_number,
                'invoice_url' => $item->invoice_pdf,
                'paid_at' => $item->paid_at ? $item->paid_at->format('Y-m-d H:i:s') : null,
                'created_at' => $item->created_at->format('Y-m-d H:i:s'),
            ];
        });

        return $this->sendResponse($histories, 'Payment histories fetched successfully.');
    }

    /**
     * Fetch complete payment entity details from Razorpay API.
     */
    private function fetchRazorpayPaymentDetails(string $paymentId): ?array
    {
        $keyId = (string) config('services.razorpay.key_id');
        $keySecret = (string) config('services.razorpay.key_secret');

        if (empty($keyId) || empty($keySecret)) {
            return null;
        }

        try {
            $response = Http::withBasicAuth($keyId, $keySecret)
                ->get("https://api.razorpay.com/v1/payments/{$paymentId}");

            return $response->successful() ? $response->json() : null;
        } catch (Exception $e) {
            Log::error('Fetch payment details failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Helper to process success, parse payment methods, create Razorpay invoice and update DB.
     */
    private function processPaymentSuccess(
        PaymentHistory $paymentHistory,
        string $paymentId,
        ?string $signature = null,
        ?array $paymentDetails = null
    ): void {
        if ($paymentHistory->status === 'paid' && !empty($paymentHistory->invoice_pdf)) {
            return;
        }

        $updateData = [
            'razorpay_payment_id' => $paymentId,
            'razorpay_signature' => $signature ?? $paymentHistory->razorpay_signature,
            'status' => 'paid',
            'paid_at' => now(),
        ];

        // Parse Razorpay Payment Details (Method, Card, UPI, Bank, Fee, Tax)
        if ($paymentDetails) {
            $updateData['payment_method'] = $paymentDetails['method'] ?? null; // card, upi, netbanking, wallet, emi
            $updateData['email'] = $paymentDetails['email'] ?? $paymentHistory->email;
            $updateData['contact'] = $paymentDetails['contact'] ?? $paymentHistory->contact;
            $updateData['fee'] = isset($paymentDetails['fee']) ? ($paymentDetails['fee'] / 100) : null;
            $updateData['tax'] = isset($paymentDetails['tax']) ? ($paymentDetails['tax'] / 100) : null;
            $updateData['gateway_response'] = $paymentDetails;

            // Specific method attributes
            if (($paymentDetails['method'] ?? '') === 'card' && isset($paymentDetails['card'])) {
                $card = $paymentDetails['card'];
                $updateData['card_network'] = $card['network'] ?? null; // Visa, MasterCard, RuPay
                $updateData['card_last4'] = $card['last4'] ?? null;
            } elseif (($paymentDetails['method'] ?? '') === 'upi') {
                $updateData['vpa'] = $paymentDetails['vpa'] ?? null; // UPI ID (e.g. user@okaxis)
            } elseif (($paymentDetails['method'] ?? '') === 'netbanking') {
                $updateData['bank'] = $paymentDetails['bank'] ?? null; // Bank Code (e.g. HDFC, SBIN)
            } elseif (($paymentDetails['method'] ?? '') === 'wallet') {
                $updateData['wallet'] = $paymentDetails['wallet'] ?? null; // e.g. paytm, mobikwik
            }
        }

        $paymentHistory->update($updateData);

        // Generate Invoice using Razorpay Invoices API
        $this->generateRazorpayInvoice($paymentHistory);
    }

    /**
     * Generate Invoice via Razorpay Invoices API (POST /v1/invoices).
     */
    private function generateRazorpayInvoice(PaymentHistory $paymentHistory): void
    {
        $keyId = (string) config('services.razorpay.key_id');
        $keySecret = (string) config('services.razorpay.key_secret');

        if (empty($keyId) || empty($keySecret)) {
            return;
        }

        try {
            $user = $paymentHistory->user ?? User::find($paymentHistory->user_id);
            $amountPaise = (int) round($paymentHistory->amount * 100);

            $customerData = [
                'name' => $user->name ?? 'Customer',
                'contact' => $user->mobile ? ('+91' . $user->mobile) : null,
                'email' => $user->email ?? null,
            ];

            // Remove null entries
            $customerData = array_filter($customerData);

            $invoicePayload = [
                'type' => 'invoice',
                'description' => $paymentHistory->purpose ?? 'Service Invoice',
                'customer' => $customerData,
                'line_items' => [
                    [
                        'name' => $paymentHistory->purpose ?? 'Service Payment',
                        'amount' => $amountPaise,
                        'currency' => 'INR',
                        'quantity' => 1,
                    ],
                ],
                'currency' => 'INR',
                'receipt' => 'INV_' . time() . '_' . $paymentHistory->id,
            ];

            $response = Http::withBasicAuth($keyId, $keySecret)
                ->post('https://api.razorpay.com/v1/invoices', $invoicePayload);

            if ($response->successful()) {
                $invoiceData = $response->json();

                $invoiceNumber = $invoiceData['invoice_number'] ?? $invoiceData['id'];
                $invoiceUrl = $invoiceData['short_url'] ?? null;

                $paymentHistory->update([
                    'invoice_number' => $invoiceNumber,
                    'invoice_pdf' => $invoiceUrl,
                ]);
            } else {
                Log::error('Razorpay invoice generation failed', [
                    'payment_history_id' => $paymentHistory->id,
                    'response' => $response->json() ?? $response->body(),
                ]);
            }
        } catch (Exception $e) {
            Log::error('Razorpay Invoice generation exception: ' . $e->getMessage());
        }
    }
}
