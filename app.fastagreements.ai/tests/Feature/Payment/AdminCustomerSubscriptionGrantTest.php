<?php

namespace Tests\Feature\Payment;

use App\Models\CustomerSubscription;
use App\Models\User;
use App\Services\AgreementEntitlementService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminCustomerSubscriptionGrantTest extends TestCase
{
    use DatabaseTransactions;

    private function admin(): User
    {
        return User::first() ?? User::create([
            'name' => 'Admin',
            'email' => 'admin-grant-test@example.com',
            'password' => bcrypt('password'),
        ]);
    }

    private function makeCustomer(): int
    {
        return DB::table('customers')->insertGetId([
            'name' => 'Grantee', 'is_active' => 1,
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

    public function test_an_admin_granted_monthly_subscription_yields_an_entitlement(): void
    {
        $customerId = $this->makeCustomer();
        $planId = $this->makePlan();

        $this->actingAs($this->admin())->post(route('customer-subscriptions.store'), [
            'customer_id' => $customerId,
            'subscription_plan_id' => $planId,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addMonth()->toDateString(),
        ])->assertRedirect(route('customer-subscriptions.index'));

        $subscription = CustomerSubscription::where('customer_id', $customerId)->first();
        $this->assertNotNull($subscription);

        // Time-based plans grant unlimited (null), never the column's
        // DEFAULT 0 — which AgreementEntitlementService reads as exhausted.
        $this->assertNull($subscription->remaining_agreements);

        $entitlement = app(AgreementEntitlementService::class)->getEntitlement($customerId);

        $this->assertNotNull($entitlement, 'A manually granted monthly subscription must yield an entitlement.');
        $this->assertSame($subscription->id, $entitlement['subscription_id']);
    }

    public function test_an_admin_granted_per_agreement_subscription_uses_the_plan_limit(): void
    {
        $customerId = $this->makeCustomer();
        $planId = $this->makePlan([
            'name' => 'Five Pack', 'duration_type' => 'per_agreement',
            'duration_value' => 0, 'agreement_limit' => 5,
        ]);

        $this->actingAs($this->admin())->post(route('customer-subscriptions.store'), [
            'customer_id' => $customerId,
            'subscription_plan_id' => $planId,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addYear()->toDateString(),
        ]);

        $subscription = CustomerSubscription::where('customer_id', $customerId)->first();
        $this->assertSame(5, $subscription->remaining_agreements);
    }

    public function test_updating_an_admin_granted_subscription_also_sets_remaining_agreements(): void
    {
        $customerId = $this->makeCustomer();
        $planId = $this->makePlan();

        $subscriptionId = DB::table('user_subscriptions')->insertGetId([
            'customer_id' => $customerId, 'subscription_plan_id' => $planId,
            'start_date' => now(), 'end_date' => now()->addMonth(),
            'is_active' => 1, 'remaining_agreements' => 0,
            'created_at' => now(), 'updated_at' => now(),
        ]);

        $this->actingAs($this->admin())->put(route('customer-subscriptions.update', $subscriptionId), [
            'customer_id' => $customerId,
            'subscription_plan_id' => $planId,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addMonth()->toDateString(),
        ]);

        $this->assertNull(
            DB::table('user_subscriptions')->where('id', $subscriptionId)->value('remaining_agreements')
        );
    }
}
