<?php

namespace Tests\Feature\Payment;

use App\Models\PaymentOrder;
use App\Models\SubscriptionPlan;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PaymentOrderModelTest extends TestCase
{
    use DatabaseTransactions;

    private function makeCustomer(): int
    {
        return DB::table('customers')->insertGetId([
            'name' => 'Payer', 'is_active' => 1,
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    private function makePlan(array $overrides = []): int
    {
        return DB::table('subscription_plans')->insertGetId(array_merge([
            'name' => 'Monthly', 'price' => 399,
            'duration_type' => 'monthly', 'duration_value' => 1,
            'is_active' => 1, 'created_at' => now(), 'updated_at' => now(),
        ], $overrides));
    }

    public function test_it_persists_and_reads_back_an_order(): void
    {
        $order = PaymentOrder::create([
            'customer_id' => $this->makeCustomer(),
            'subscription_plan_id' => $this->makePlan(),
            'status' => PaymentOrder::STATUS_CREATED,
            'amount_paise' => 39900,
            'currency' => 'INR',
            'expires_at' => now()->addMinutes(15),
        ]);

        $this->assertSame(39900, $order->fresh()->amount_paise);
        $this->assertFalse($order->isPaid());
    }

    public function test_amount_paise_is_an_integer_not_a_string(): void
    {
        $order = PaymentOrder::create([
            'customer_id' => $this->makeCustomer(),
            'subscription_plan_id' => $this->makePlan(),
            'amount_paise' => 1500,
            'expires_at' => now()->addMinutes(15),
        ]);

        $this->assertIsInt($order->fresh()->amount_paise);
    }

    public function test_is_paid_reflects_status(): void
    {
        $order = new PaymentOrder(['status' => PaymentOrder::STATUS_PAID]);
        $this->assertTrue($order->isPaid());
    }

    public function test_is_expired_is_true_past_expires_at_while_unpaid(): void
    {
        $order = new PaymentOrder([
            'status' => PaymentOrder::STATUS_CREATED,
            'expires_at' => now()->subMinute(),
        ]);

        $this->assertTrue($order->isExpired());
    }

    public function test_a_paid_order_is_never_expired(): void
    {
        $order = new PaymentOrder([
            'status' => PaymentOrder::STATUS_PAID,
            'expires_at' => now()->subDay(),
        ]);

        $this->assertFalse($order->isExpired());
    }

    public function test_it_belongs_to_a_plan(): void
    {
        $planId = $this->makePlan(['name' => 'Yearly', 'price' => 10300]);

        $order = PaymentOrder::create([
            'customer_id' => $this->makeCustomer(),
            'subscription_plan_id' => $planId,
            'amount_paise' => 1030000,
            'expires_at' => now()->addMinutes(15),
        ]);

        $this->assertSame('Yearly', $order->plan->name);
    }

    public function test_plan_exposes_otp_mode_constants(): void
    {
        $this->assertSame('with_otp', SubscriptionPlan::OTP_WITH);
        $this->assertSame('without_otp', SubscriptionPlan::OTP_WITHOUT);
    }
}
