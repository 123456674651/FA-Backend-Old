<?php

namespace Tests\Feature\Payment;

use App\Models\PaymentOrder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ExpireStalePaymentOrdersTest extends TestCase
{
    use DatabaseTransactions;

    private function makeOrder(string $status, \DateTimeInterface $expiresAt): PaymentOrder
    {
        $customerId = DB::table('customers')->insertGetId([
            'name' => 'Payer', 'is_active' => 1,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $planId = DB::table('subscription_plans')->insertGetId([
            'name' => 'Monthly', 'price' => 399, 'duration_type' => 'monthly',
            'duration_value' => 1, 'is_active' => 1,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        return PaymentOrder::create([
            'customer_id' => $customerId,
            'subscription_plan_id' => $planId,
            'status' => $status,
            'amount_paise' => 39900,
            'razorpay_order_id' => 'order_' . uniqid(),
            'expires_at' => $expiresAt,
        ]);
    }

    public function test_it_expires_an_abandoned_order(): void
    {
        $order = $this->makeOrder(PaymentOrder::STATUS_CREATED, now()->subHour());

        $this->artisan('payments:expire-stale')->assertExitCode(0);

        $this->assertSame(PaymentOrder::STATUS_EXPIRED, $order->fresh()->status);
    }

    public function test_it_leaves_an_order_that_is_still_open(): void
    {
        $order = $this->makeOrder(PaymentOrder::STATUS_CREATED, now()->addMinutes(10));

        $this->artisan('payments:expire-stale');

        $this->assertSame(PaymentOrder::STATUS_CREATED, $order->fresh()->status);
    }

    /** A payment captured after the TTL must never be expired out from under the customer. */
    public function test_it_never_touches_a_paid_order(): void
    {
        $order = $this->makeOrder(PaymentOrder::STATUS_PAID, now()->subDay());

        $this->artisan('payments:expire-stale');

        $this->assertSame(PaymentOrder::STATUS_PAID, $order->fresh()->status);
    }

    public function test_it_never_touches_a_failed_order(): void
    {
        $order = $this->makeOrder(PaymentOrder::STATUS_FAILED, now()->subDay());

        $this->artisan('payments:expire-stale');

        $this->assertSame(PaymentOrder::STATUS_FAILED, $order->fresh()->status);
    }
}
