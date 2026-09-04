<?php

namespace Tests\Feature\Payment;

use App\Models\SubscriptionPlan;
use App\Services\AgreementEntitlementService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class EntitlementOtpCoverageTest extends TestCase
{
    use DatabaseTransactions;

    private AgreementEntitlementService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(AgreementEntitlementService::class);
    }

    private function makeCustomer(): int
    {
        return DB::table('customers')->insertGetId([
            'name' => 'Subscriber', 'is_active' => 1,
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    private function giveSubscription(int $customerId, ?string $otpMode, ?int $remaining = null): int
    {
        $planId = DB::table('subscription_plans')->insertGetId([
            'name' => 'P', 'price' => 100, 'duration_type' => 'monthly',
            'duration_value' => 1, 'otp_mode' => $otpMode, 'is_active' => 1,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        return DB::table('user_subscriptions')->insertGetId([
            'customer_id' => $customerId,
            'subscription_plan_id' => $planId,
            'otp_mode' => $otpMode,
            'start_date' => now()->subDay(),
            'end_date' => now()->addMonth(),
            'remaining_agreements' => $remaining,
            'is_active' => 1,
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    public function test_without_otp_plan_does_not_cover_a_with_otp_agreement(): void
    {
        $customer = $this->makeCustomer();
        $this->giveSubscription($customer, SubscriptionPlan::OTP_WITHOUT);

        $this->assertNull($this->service->getEntitlement($customer, SubscriptionPlan::OTP_WITH));
    }

    public function test_without_otp_plan_covers_a_without_otp_agreement(): void
    {
        $customer = $this->makeCustomer();
        $this->giveSubscription($customer, SubscriptionPlan::OTP_WITHOUT);

        $this->assertNotNull($this->service->getEntitlement($customer, SubscriptionPlan::OTP_WITHOUT));
    }

    /** They paid the higher price, so the cheaper agreement is covered. */
    public function test_with_otp_plan_covers_a_without_otp_agreement(): void
    {
        $customer = $this->makeCustomer();
        $this->giveSubscription($customer, SubscriptionPlan::OTP_WITH);

        $this->assertNotNull($this->service->getEntitlement($customer, SubscriptionPlan::OTP_WITHOUT));
    }

    public function test_with_otp_plan_covers_a_with_otp_agreement(): void
    {
        $customer = $this->makeCustomer();
        $this->giveSubscription($customer, SubscriptionPlan::OTP_WITH);

        $this->assertNotNull($this->service->getEntitlement($customer, SubscriptionPlan::OTP_WITH));
    }

    /** NULL predates the feature and must not be narrowed. */
    public function test_null_otp_mode_covers_both(): void
    {
        $customer = $this->makeCustomer();
        $this->giveSubscription($customer, null);

        $this->assertNotNull($this->service->getEntitlement($customer, SubscriptionPlan::OTP_WITH));
        $this->assertNotNull($this->service->getEntitlement($customer, SubscriptionPlan::OTP_WITHOUT));
    }

    public function test_omitting_otp_mode_keeps_the_old_behaviour(): void
    {
        $customer = $this->makeCustomer();
        $this->giveSubscription($customer, SubscriptionPlan::OTP_WITHOUT);

        $this->assertNotNull($this->service->getEntitlement($customer));
    }

    public function test_an_exhausted_quota_is_still_not_covered(): void
    {
        $customer = $this->makeCustomer();
        $this->giveSubscription($customer, SubscriptionPlan::OTP_WITH, 0);

        $this->assertNull($this->service->getEntitlement($customer, SubscriptionPlan::OTP_WITH));
    }

    /**
     * I3: an active without_otp monthly plan and a spent with_otp
     * per-agreement credit can now coexist (Task 11's tier-scoped
     * supersede). The credit, being the higher-id row, must not shadow the
     * still-active plan underneath it for a plain without_otp agreement.
     */
    public function test_a_spent_credit_does_not_shadow_an_active_plan_underneath_it(): void
    {
        $customer = $this->makeCustomer();

        // Older row: an active without_otp monthly.
        $this->giveSubscription($customer, SubscriptionPlan::OTP_WITHOUT);

        // Newer row: a with_otp per-agreement credit, already spent.
        $this->giveSubscription($customer, SubscriptionPlan::OTP_WITH, 0);

        $entitlement = $this->service->getEntitlement($customer, SubscriptionPlan::OTP_WITHOUT);

        $this->assertNotNull($entitlement);
    }
}
