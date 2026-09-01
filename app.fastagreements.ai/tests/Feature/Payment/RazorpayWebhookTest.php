<?php

namespace Tests\Feature\Payment;

use App\Models\CustomerSubscription;
use App\Models\PaymentOrder;
use App\Models\SubscriptionInvoice;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RazorpayWebhookTest extends TestCase
{
    use DatabaseTransactions;

    private const WEBHOOK_SECRET = 'test_webhook_secret';

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.razorpay.webhook_secret' => self::WEBHOOK_SECRET]);
    }

    private function makeOrder(string $razorpayOrderId): PaymentOrder
    {
        $customerId = DB::table('customers')->insertGetId([
            'name' => 'Payer', 'is_active' => 1,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $planId = DB::table('subscription_plans')->insertGetId([
            'name' => 'Monthly', 'price' => 399.00,
            'duration_type' => 'monthly', 'duration_value' => 1,
            'otp_mode' => null, 'is_active' => 1,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        return PaymentOrder::create([
            'customer_id' => $customerId,
            'subscription_plan_id' => $planId,
            'status' => PaymentOrder::STATUS_CREATED,
            'amount_paise' => 39900,
            'currency' => 'INR',
            'razorpay_order_id' => $razorpayOrderId,
            'expires_at' => now()->addMinutes(15),
        ]);
    }

    private function payload(string $event, string $orderId, string $paymentId, string $errorDescription = ''): string
    {
        return json_encode([
            'event' => $event,
            'payload' => [
                'payment' => [
                    'entity' => [
                        'id' => $paymentId,
                        'order_id' => $orderId,
                        'error_description' => $errorDescription,
                    ],
                ],
            ],
        ], JSON_THROW_ON_ERROR);
    }

    private function send(string $body, ?string $signature = null): \Illuminate\Testing\TestResponse
    {
        return $this->call(
            'POST',
            '/api/webhooks/razorpay',
            [], [], [],
            [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_X_RAZORPAY_SIGNATURE' => $signature ?? hash_hmac('sha256', $body, self::WEBHOOK_SECRET),
            ],
            $body,
        );
    }

    public function test_it_needs_no_auth_token(): void
    {
        $order = $this->makeOrder('order_W1');
        $body = $this->payload('payment.captured', 'order_W1', 'pay_W1');

        $this->send($body)->assertOk();
    }

    public function test_a_captured_payment_fulfils_the_order(): void
    {
        $order = $this->makeOrder('order_W2');

        $this->send($this->payload('payment.captured', 'order_W2', 'pay_W2'))->assertOk();

        $this->assertSame(PaymentOrder::STATUS_PAID, $order->fresh()->status);
        $this->assertSame(1, SubscriptionInvoice::where('payment_order_id', $order->id)->count());
    }

    public function test_a_forged_signature_is_rejected_and_grants_nothing(): void
    {
        $order = $this->makeOrder('order_W3');
        $body = $this->payload('payment.captured', 'order_W3', 'pay_W3');

        $this->send($body, hash_hmac('sha256', $body, 'attacker_secret'))->assertStatus(401);

        $this->assertSame(PaymentOrder::STATUS_CREATED, $order->fresh()->status);
        $this->assertSame(0, CustomerSubscription::where('customer_id', $order->customer_id)->count());
    }

    public function test_a_missing_signature_header_is_rejected(): void
    {
        $this->makeOrder('order_W4');
        $body = $this->payload('payment.captured', 'order_W4', 'pay_W4');

        $this->call('POST', '/api/webhooks/razorpay', [], [], [], ['CONTENT_TYPE' => 'application/json'], $body)
            ->assertStatus(401);
    }

    /** The signature covers the exact bytes; a modified body must not verify. */
    public function test_a_body_altered_after_signing_is_rejected(): void
    {
        $order = $this->makeOrder('order_W5');
        $signed = $this->payload('payment.captured', 'order_W5', 'pay_W5');
        $signature = hash_hmac('sha256', $signed, self::WEBHOOK_SECRET);

        $this->send($this->payload('payment.captured', 'order_W5', 'pay_TAMPERED'), $signature)
            ->assertStatus(401);

        $this->assertSame(PaymentOrder::STATUS_CREATED, $order->fresh()->status);
    }

    public function test_a_failed_payment_is_recorded_without_granting_anything(): void
    {
        $order = $this->makeOrder('order_W6');

        $this->send($this->payload('payment.failed', 'order_W6', 'pay_W6', 'card declined'))->assertOk();

        $this->assertSame(PaymentOrder::STATUS_FAILED, $order->fresh()->status);
        $this->assertStringContainsString('declined', $order->fresh()->failure_reason);
        $this->assertSame(0, CustomerSubscription::where('customer_id', $order->customer_id)->count());
    }

    /** The app callback usually wins the race; the webhook must be a no-op. */
    public function test_a_webhook_after_the_callback_changes_nothing(): void
    {
        $order = $this->makeOrder('order_W7');
        app(\App\Services\Payment\PaymentFulfilmentService::class)->fulfil($order, 'pay_W7');

        $this->send($this->payload('payment.captured', 'order_W7', 'pay_W7'))->assertOk();

        $this->assertSame(1, CustomerSubscription::where('customer_id', $order->customer_id)->count());
        $this->assertSame(1, SubscriptionInvoice::where('payment_order_id', $order->id)->count());
    }

    public function test_an_unknown_order_is_acknowledged_not_retried(): void
    {
        $this->send($this->payload('payment.captured', 'order_NEVER_SEEN', 'pay_X'))->assertOk();
    }

    public function test_an_irrelevant_event_is_acknowledged(): void
    {
        $this->send($this->payload('payment.authorized', 'order_W8', 'pay_W8'))->assertOk();
    }

    /**
     * Guards against computing the HMAC over json_encode($request->all())
     * instead of $request->getContent(). The raw body here is pretty-printed
     * (real whitespace between tokens); json_decode() discards that
     * whitespace, and Laravel's default json_encode() re-serialises compact,
     * so a decode/re-encode round trip does not reproduce the original
     * bytes. A signature computed over the re-encoded array would therefore
     * fail to match one computed over the raw body Razorpay actually sent.
     */
    public function test_the_signature_is_verified_over_raw_bytes_not_a_reencoded_array(): void
    {
        $order = $this->makeOrder('order_W9');

        $body = json_encode([
            'event' => 'payment.captured',
            'payload' => [
                'payment' => [
                    'entity' => [
                        'id' => 'pay_W9',
                        'order_id' => 'order_W9',
                        'error_description' => '',
                    ],
                ],
            ],
        ], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);

        // Confirm the fixture actually exercises the divergence this test
        // exists to catch; otherwise it would be no stronger than the others.
        $this->assertNotSame($body, json_encode(json_decode($body, true)));

        $this->send($body, hash_hmac('sha256', $body, self::WEBHOOK_SECRET))->assertOk();

        $this->assertSame(PaymentOrder::STATUS_PAID, $order->fresh()->status);
    }
}
