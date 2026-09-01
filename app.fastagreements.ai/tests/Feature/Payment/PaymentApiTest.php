<?php

namespace Tests\Feature\Payment;

use App\Models\CustomerSubscription;
use App\Models\PaymentOrder;
use App\Models\SubscriptionInvoice;
use App\Services\Auth\JwtService;
use App\Services\Payment\RazorpayGateway;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\Support\FakeRazorpayGateway;
use Tests\TestCase;

class PaymentApiTest extends TestCase
{
    use DatabaseTransactions;

    private const KEY_SECRET = 'test_key_secret';

    private FakeRazorpayGateway $gateway;

    protected function setUp(): void
    {
        parent::setUp();

        $this->gateway = new FakeRazorpayGateway();
        $this->app->instance(RazorpayGateway::class, $this->gateway);

        config([
            'services.razorpay.key_id' => 'rzp_test_public',
            'services.razorpay.key_secret' => self::KEY_SECRET,
            'services.razorpay.webhook_secret' => 'test_webhook_secret',
        ]);
    }

    private function actingAsCustomer(int $id): self
    {
        return $this->withHeader('Authorization', 'Bearer ' . app(JwtService::class)->issueForCustomer($id));
    }

    private function makeCustomer(string $name = 'Buyer'): int
    {
        return DB::table('customers')->insertGetId([
            'name' => $name, 'is_active' => 1,
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    private function makePlan(array $overrides = []): int
    {
        return DB::table('subscription_plans')->insertGetId(array_merge([
            'name' => 'Monthly', 'price' => 399.00,
            'duration_type' => 'monthly', 'duration_value' => 1,
            'otp_mode' => null, 'is_active' => 1,
            'created_at' => now(), 'updated_at' => now(),
        ], $overrides));
    }

    private function signature(string $orderId, string $paymentId): string
    {
        return hash_hmac('sha256', $orderId . '|' . $paymentId, self::KEY_SECRET);
    }

    public function test_the_order_endpoint_requires_authentication(): void
    {
        $this->postJson('/api/payment/order', ['subscription_plan_id' => $this->makePlan()])
            ->assertUnauthorized();
    }

    public function test_it_creates_an_order_and_returns_the_public_key(): void
    {
        $this->gateway->nextOrderId = 'order_API1';

        $this->actingAsCustomer($this->makeCustomer())
            ->postJson('/api/payment/order', ['subscription_plan_id' => $this->makePlan()])
            ->assertOk()
            ->assertJson([
                'payment_required' => true,
                'razorpay_order_id' => 'order_API1',
                'amount' => 39900,
                'currency' => 'INR',
                'key_id' => 'rzp_test_public',
            ]);
    }

    public function test_the_response_never_contains_the_key_secret(): void
    {
        $response = $this->actingAsCustomer($this->makeCustomer())
            ->postJson('/api/payment/order', ['subscription_plan_id' => $this->makePlan()]);

        $this->assertStringNotContainsString(self::KEY_SECRET, $response->getContent());
    }

    /** The amount is the plan's, whatever the caller claims. */
    public function test_a_client_supplied_amount_is_ignored(): void
    {
        $this->actingAsCustomer($this->makeCustomer())
            ->postJson('/api/payment/order', [
                'subscription_plan_id' => $this->makePlan(),
                'amount' => 1,
                'amount_paise' => 1,
                'price' => 1,
            ])
            ->assertOk()
            ->assertJson(['amount' => 39900]);

        $this->assertSame(39900, $this->gateway->createdOrders[0]['amount_paise']);
    }

    /** Identity comes from the token, so naming another customer changes nothing. */
    public function test_a_customer_id_in_the_body_is_ignored(): void
    {
        $caller = $this->makeCustomer('Caller');
        $victim = $this->makeCustomer('Victim');

        $this->actingAsCustomer($caller)->postJson('/api/payment/order', [
            'subscription_plan_id' => $this->makePlan(),
            'customer_id' => $victim,
        ])->assertOk();

        $this->assertSame(1, PaymentOrder::where('customer_id', $caller)->count());
        $this->assertSame(0, PaymentOrder::where('customer_id', $victim)->count());
    }

    public function test_a_gateway_failure_returns_a_502_and_creates_no_order(): void
    {
        $this->gateway->throwOnCreate = true;

        $this->actingAsCustomer($this->makeCustomer())
            ->postJson('/api/payment/order', ['subscription_plan_id' => $this->makePlan()])
            ->assertStatus(502);

        $this->assertSame(0, PaymentOrder::count());
    }

    public function test_an_invalid_otp_mode_is_rejected(): void
    {
        $this->actingAsCustomer($this->makeCustomer())
            ->postJson('/api/payment/order', [
                'subscription_plan_id' => $this->makePlan(),
                'otp_mode' => 'not_a_real_mode',
            ])
            ->assertStatus(422);
    }

    public function test_an_unknown_plan_is_rejected(): void
    {
        $this->actingAsCustomer($this->makeCustomer())
            ->postJson('/api/payment/order', ['subscription_plan_id' => 99999999])
            ->assertStatus(422);
    }

    public function test_a_missing_plan_id_is_rejected(): void
    {
        $this->actingAsCustomer($this->makeCustomer())
            ->postJson('/api/payment/order', [])
            ->assertStatus(422);
    }

    public function test_verify_accepts_a_genuine_signature_and_grants_the_plan(): void
    {
        $customer = $this->makeCustomer();
        $this->gateway->nextOrderId = 'order_V1';

        $this->actingAsCustomer($customer)
            ->postJson('/api/payment/order', ['subscription_plan_id' => $this->makePlan()])
            ->assertOk();

        $response = $this->actingAsCustomer($customer)->postJson('/api/payment/verify', [
            'razorpay_order_id' => 'order_V1',
            'razorpay_payment_id' => 'pay_V1',
            'razorpay_signature' => $this->signature('order_V1', 'pay_V1'),
        ])->assertOk()->assertJson(['status' => 'success']);

        $this->assertSame(
            PaymentOrder::STATUS_PAID,
            PaymentOrder::where('razorpay_order_id', 'order_V1')->value('status')
        );
        $this->assertSame(1, SubscriptionInvoice::where('customer_id', $customer)->count());

        $order = PaymentOrder::where('razorpay_order_id', 'order_V1')->first();
        $subscription = CustomerSubscription::where('payment_order_id', $order->id)->first();

        $this->assertNotNull($subscription);
        $this->assertSame($subscription->id, $response->json('subscription_id'));
        $this->assertNotSame($order->subscription_plan_id, $response->json('subscription_id'));
    }

    public function test_verify_rejects_a_forged_signature_and_grants_nothing(): void
    {
        $customer = $this->makeCustomer();
        $this->gateway->nextOrderId = 'order_V2';

        $this->actingAsCustomer($customer)
            ->postJson('/api/payment/order', ['subscription_plan_id' => $this->makePlan()])
            ->assertOk();

        $this->actingAsCustomer($customer)->postJson('/api/payment/verify', [
            'razorpay_order_id' => 'order_V2',
            'razorpay_payment_id' => 'pay_V2',
            'razorpay_signature' => hash_hmac('sha256', 'order_V2|pay_V2', 'attacker_secret'),
        ])->assertStatus(422);

        $this->assertSame(
            PaymentOrder::STATUS_CREATED,
            PaymentOrder::where('razorpay_order_id', 'order_V2')->value('status')
        );
        $this->assertSame(0, SubscriptionInvoice::where('customer_id', $customer)->count());
    }

    public function test_verify_refuses_an_order_belonging_to_someone_else(): void
    {
        $owner = $this->makeCustomer('Owner');
        $attacker = $this->makeCustomer('Attacker');
        $this->gateway->nextOrderId = 'order_V3';

        $this->actingAsCustomer($owner)
            ->postJson('/api/payment/order', ['subscription_plan_id' => $this->makePlan()])
            ->assertOk();

        $this->actingAsCustomer($attacker)->postJson('/api/payment/verify', [
            'razorpay_order_id' => 'order_V3',
            'razorpay_payment_id' => 'pay_V3',
            'razorpay_signature' => $this->signature('order_V3', 'pay_V3'),
        ])->assertStatus(404);

        $this->assertSame(0, SubscriptionInvoice::where('customer_id', $attacker)->count());
    }

    public function test_verify_is_idempotent(): void
    {
        $customer = $this->makeCustomer();
        $this->gateway->nextOrderId = 'order_V4';

        $this->actingAsCustomer($customer)
            ->postJson('/api/payment/order', ['subscription_plan_id' => $this->makePlan()])
            ->assertOk();

        $payload = [
            'razorpay_order_id' => 'order_V4',
            'razorpay_payment_id' => 'pay_V4',
            'razorpay_signature' => $this->signature('order_V4', 'pay_V4'),
        ];

        $this->actingAsCustomer($customer)->postJson('/api/payment/verify', $payload)->assertOk();
        $this->actingAsCustomer($customer)->postJson('/api/payment/verify', $payload)->assertOk();

        $this->assertSame(1, SubscriptionInvoice::where('customer_id', $customer)->count());
    }
}
