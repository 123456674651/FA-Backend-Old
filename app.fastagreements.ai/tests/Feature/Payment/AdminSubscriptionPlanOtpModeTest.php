<?php

namespace Tests\Feature\Payment;

use App\Models\SubscriptionPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminSubscriptionPlanOtpModeTest extends TestCase
{
    use DatabaseTransactions;

    private function admin(): User
    {
        return User::first() ?? User::create([
            'name' => 'Admin',
            'email' => 'admin-otp-test@example.com',
            'password' => bcrypt('password'),
        ]);
    }

    public function test_an_admin_can_create_a_with_otp_plan(): void
    {
        $this->actingAs($this->admin())->post(route('subscription-plans.store'), [
            'name' => 'Per Agreement With OTP',
            'price' => 20,
            'duration_type' => 'per_agreement',
            'duration_value' => null,
            'agreement_limit' => 1,
            'otp_mode' => SubscriptionPlan::OTP_WITH,
            'is_active' => 1,
        ]);

        $this->assertSame(
            SubscriptionPlan::OTP_WITH,
            DB::table('subscription_plans')->where('name', 'Per Agreement With OTP')->value('otp_mode')
        );
    }

    public function test_an_admin_can_update_a_plan_to_without_otp(): void
    {
        $planId = DB::table('subscription_plans')->insertGetId([
            'name' => 'Monthly', 'price' => 399, 'duration_type' => 'monthly',
            'duration_value' => 1, 'otp_mode' => SubscriptionPlan::OTP_WITH,
            'is_active' => 1, 'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->actingAs($this->admin())->put(route('subscription-plans.update', $planId), [
            'name' => 'Monthly',
            'price' => 299,
            'duration_type' => 'monthly',
            'duration_value' => 1,
            'otp_mode' => SubscriptionPlan::OTP_WITHOUT,
            'is_active' => 1,
        ]);

        $this->assertSame(
            SubscriptionPlan::OTP_WITHOUT,
            DB::table('subscription_plans')->where('id', $planId)->value('otp_mode')
        );
    }

    /** Blank means "covers both" and must be storable, not coerced to a tier. */
    public function test_leaving_otp_mode_blank_stores_null(): void
    {
        $this->actingAs($this->admin())->post(route('subscription-plans.store'), [
            'name' => 'Covers Both',
            'price' => 500,
            'duration_type' => 'monthly',
            'duration_value' => 1,
            'otp_mode' => '',
            'is_active' => 1,
        ]);

        $this->assertNull(
            DB::table('subscription_plans')->where('name', 'Covers Both')->value('otp_mode')
        );
    }

    public function test_an_invalid_otp_mode_is_rejected(): void
    {
        $this->actingAs($this->admin())->post(route('subscription-plans.store'), [
            'name' => 'Bogus',
            'price' => 100,
            'duration_type' => 'monthly',
            'duration_value' => 1,
            'otp_mode' => 'sometimes',
            'is_active' => 1,
        ])->assertSessionHasErrors('otp_mode');

        $this->assertSame(0, DB::table('subscription_plans')->where('name', 'Bogus')->count());
    }
}
