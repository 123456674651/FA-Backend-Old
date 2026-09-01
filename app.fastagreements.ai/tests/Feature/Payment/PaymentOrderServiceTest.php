<?php

namespace Tests\Feature\Payment;

use App\Models\PaymentOrder;
use App\Models\SubscriptionPlan;
use App\Services\Payment\PaymentException;
use App\Services\Payment\PaymentOrderService;
use App\Services\Payment\RazorpayGateway;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\Support\FakeRazorpayGateway;
use Tests\TestCase;

class PaymentOrderServiceTest extends TestCase
{
    use DatabaseTransactions;

    private FakeRazorpayGateway $gateway;

    protected function setUp(): void
    {
        parent::setUp();

        $this->gateway = new FakeRazorpayGateway();
        $this->app->instance(RazorpayGateway::class, $this->gateway);
        config(['services.razorpay.key_id' => 'rzp_test_public']);
    }

    private function service(): PaymentOrderService
    {
        return app(PaymentOrderService::class);
    }

    private function makeCustomer(): int
    {
        return DB::table('customers')->insertGetId([
            'name' => 'Buyer', 'is_active' => 1,
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

    private function givePerAgreementCredit(int $customerId, ?string $otpMode): void
    {
        $planId = $this->makePlan(['duration_type' => 'per_agreement', 'price' => 15, 'otp_mode' => $otpMode]);

        DB::table('user_subscriptions')->insert([
            'customer_id' => $customerId,
            'subscription_plan_id' => $planId,
            'otp_mode' => $otpMode,
            'start_date' => now()->subDay(),
            'end_date' => null,
            'remaining_agreements' => 1,
            'is_active' => 1,
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    public function test_it_prices_the_order_from_the_plan_not_the_caller(): void
    {
        $result = $this->service()->createFor($this->makeCustomer(), $this->makePlan(), null);

        $this->assertTrue($result['payment_required']);
        $this->assertSame(39900, $result['order']->amount_paise);
        $this->assertSame(39900, $this->gateway->createdOrders[0]['amount_paise']);
    }

    public function test_it_returns_the_public_key_id_and_never_the_secret(): void
    {
        config(['services.razorpay.key_secret' => 'super_secret']);

        $result = $this->service()->createFor($this->makeCustomer(), $this->makePlan(), null);

        $this->assertSame('rzp_test_public', $result['key_id']);
        // The secret must not appear anywhere in what the caller gets back.
        $this->assertStringNotContainsString('super_secret', json_encode([
            'payment_required' => $result['payment_required'],
            'key_id' => $result['key_id'],
            'order' => $result['order']->toArray(),
        ]));
    }

    public function test_it_stores_the_gateway_order_id(): void
    {
        $this->gateway->nextOrderId = 'order_XYZ';

        $result = $this->service()->createFor($this->makeCustomer(), $this->makePlan(), null);

        $this->assertSame('order_XYZ', $result['order']->razorpay_order_id);
        $this->assertSame(PaymentOrder::STATUS_CREATED, $result['order']->status);
    }

    public function test_the_receipt_traces_back_to_the_local_order(): void
    {
        $result = $this->service()->createFor($this->makeCustomer(), $this->makePlan(), null);

        $this->assertSame((string) $result['order']->id, $this->gateway->createdOrders[0]['receipt']);
    }

    public function test_an_inactive_plan_cannot_be_bought(): void
    {
        $this->expectException(PaymentException::class);

        $this->service()->createFor($this->makeCustomer(), $this->makePlan(['is_active' => 0]), null);
    }

    public function test_an_unknown_plan_is_rejected(): void
    {
        $this->expectException(PaymentException::class);

        $this->service()->createFor($this->makeCustomer(), 99999999, null);
    }

    public function test_a_per_agreement_purchase_requires_an_otp_mode(): void
    {
        $this->expectException(PaymentException::class);

        $planId = $this->makePlan(['duration_type' => 'per_agreement', 'price' => 15]);
        $this->service()->createFor($this->makeCustomer(), $planId, null);
    }

    public function test_a_covered_customer_is_not_charged_for_an_agreement(): void
    {
        $customer = $this->makeCustomer();
        $this->givePerAgreementCredit($customer, SubscriptionPlan::OTP_WITH);

        $planId = $this->makePlan([
            'duration_type' => 'per_agreement', 'price' => 15,
            'otp_mode' => SubscriptionPlan::OTP_WITH,
        ]);

        $result = $this->service()->createFor($customer, $planId, SubscriptionPlan::OTP_WITH);

        $this->assertFalse($result['payment_required']);
        $this->assertSame([], $this->gateway->createdOrders);
        $this->assertSame(0, PaymentOrder::where('customer_id', $customer)->count());
    }

    /**
     * The entitlement check must NOT gate subscription purchases: a customer
     * with a running plan has to be able to renew or upgrade.
     */
    public function test_a_subscriber_can_still_buy_a_plan(): void
    {
        $customer = $this->makeCustomer();
        $this->givePerAgreementCredit($customer, null);

        $result = $this->service()->createFor($customer, $this->makePlan(), null);

        $this->assertTrue($result['payment_required']);
        $this->assertCount(1, $this->gateway->createdOrders);
    }

    public function test_a_without_otp_credit_does_not_block_paying_for_a_with_otp_agreement(): void
    {
        $customer = $this->makeCustomer();
        $this->givePerAgreementCredit($customer, SubscriptionPlan::OTP_WITHOUT);

        $planId = $this->makePlan([
            'duration_type' => 'per_agreement', 'price' => 20,
            'otp_mode' => SubscriptionPlan::OTP_WITH,
        ]);

        $result = $this->service()->createFor($customer, $planId, SubscriptionPlan::OTP_WITH);

        $this->assertTrue($result['payment_required']);
        $this->assertSame(2000, $result['order']->amount_paise);
    }

    public function test_no_local_order_survives_a_gateway_failure(): void
    {
        $this->gateway->throwOnCreate = true;
        $customer = $this->makeCustomer();

        try {
            $this->service()->createFor($customer, $this->makePlan(), null);
            $this->fail('Expected a gateway failure.');
        } catch (\Throwable) {
            // expected
        }

        $this->assertSame(0, PaymentOrder::where('customer_id', $customer)->count());
    }

    public function test_the_order_expires_in_the_future(): void
    {
        $result = $this->service()->createFor($this->makeCustomer(), $this->makePlan(), null);

        $this->assertTrue($result['order']->expires_at->isFuture());
    }
}
