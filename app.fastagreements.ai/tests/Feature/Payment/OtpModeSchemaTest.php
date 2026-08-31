<?php

namespace Tests\Feature\Payment;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class OtpModeSchemaTest extends TestCase
{
    use DatabaseTransactions;

    public function test_plans_carry_an_otp_mode(): void
    {
        $this->assertTrue(Schema::hasColumn('subscription_plans', 'otp_mode'));
    }

    public function test_subscriptions_carry_a_snapshotted_otp_mode_and_order_link(): void
    {
        $this->assertTrue(Schema::hasColumn('user_subscriptions', 'otp_mode'));
        $this->assertTrue(Schema::hasColumn('user_subscriptions', 'payment_order_id'));
    }

    public function test_invoices_link_to_a_payment_order(): void
    {
        $this->assertTrue(Schema::hasColumn('subscription_invoices', 'payment_order_id'));
    }

    /**
     * NULL means "covers both modes". Existing plans have no answer, and
     * defaulting them to either mode would silently change what current
     * subscribers already paid for.
     */
    public function test_otp_mode_is_nullable_on_plans(): void
    {
        $id = DB::table('subscription_plans')->insertGetId([
            'name' => 'Legacy Plan',
            'price' => 399,
            'duration_type' => 'monthly',
            'duration_value' => 1,
            'is_active' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertNull(DB::table('subscription_plans')->where('id', $id)->value('otp_mode'));
    }
}
