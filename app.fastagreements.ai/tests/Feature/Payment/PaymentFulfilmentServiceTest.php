<?php

namespace Tests\Feature\Payment;

use App\Models\CustomerSubscription;
use App\Models\PaymentOrder;
use App\Models\SubscriptionInvoice;
use App\Models\SubscriptionPlan;
use App\Services\Payment\PaymentFulfilmentService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PaymentFulfilmentServiceTest extends TestCase
{
    use DatabaseTransactions;

    private function service(): PaymentFulfilmentService
    {
        return app(PaymentFulfilmentService::class);
    }

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
            'name' => 'Monthly', 'price' => 399.00,
            'duration_type' => 'monthly', 'duration_value' => 1,
            'agreement_limit' => null, 'otp_mode' => null, 'is_active' => 1,
            'created_at' => now(), 'updated_at' => now(),
        ], $overrides));
    }

    private function makeOrder(int $customerId, int $planId, int $paise = 39900): PaymentOrder
    {
        return PaymentOrder::create([
            'customer_id' => $customerId,
            'subscription_plan_id' => $planId,
            'status' => PaymentOrder::STATUS_CREATED,
            'amount_paise' => $paise,
            'currency' => 'INR',
            'razorpay_order_id' => 'order_' . uniqid(),
            'expires_at' => now()->addMinutes(15),
        ]);
    }

    public function test_it_activates_a_subscription_and_issues_an_invoice(): void
    {
        $customer = $this->makeCustomer();
        $order = $this->makeOrder($customer, $this->makePlan());

        $this->service()->fulfil($order, 'pay_123');

        $sub = CustomerSubscription::where('customer_id', $customer)->where('is_active', 1)->first();
        $this->assertNotNull($sub);
        $this->assertSame($order->id, $sub->payment_order_id);

        $invoice = SubscriptionInvoice::where('payment_order_id', $order->id)->first();
        $this->assertNotNull($invoice);
        $this->assertEquals(399.00, (float) $invoice->amount);
        $this->assertSame('paid', $invoice->payment_status);
    }

    public function test_it_marks_the_order_paid_and_records_the_payment_id(): void
    {
        $order = $this->makeOrder($this->makeCustomer(), $this->makePlan());

        $this->service()->fulfil($order, 'pay_abc');

        $fresh = $order->fresh();
        $this->assertSame(PaymentOrder::STATUS_PAID, $fresh->status);
        $this->assertSame('pay_abc', $fresh->razorpay_payment_id);
        $this->assertNotNull($fresh->fulfilled_at);
    }

    /** The callback and the webhook both land routinely. */
    public function test_fulfilling_twice_produces_exactly_one_subscription_and_invoice(): void
    {
        $customer = $this->makeCustomer();
        $order = $this->makeOrder($customer, $this->makePlan());

        $this->service()->fulfil($order, 'pay_123');
        $this->service()->fulfil($order->fresh(), 'pay_123');

        $this->assertSame(1, CustomerSubscription::where('customer_id', $customer)->count());
        $this->assertSame(1, SubscriptionInvoice::where('payment_order_id', $order->id)->count());
    }

    public function test_a_per_agreement_purchase_grants_one_credit(): void
    {
        $customer = $this->makeCustomer();
        $planId = $this->makePlan([
            'duration_type' => 'per_agreement', 'price' => 15,
            'agreement_limit' => 1, 'otp_mode' => SubscriptionPlan::OTP_WITH,
        ]);

        $this->service()->fulfil($this->makeOrder($customer, $planId, 1500), 'pay_1');

        $sub = CustomerSubscription::where('customer_id', $customer)->first();
        $this->assertSame(1, (int) $sub->remaining_agreements);
        $this->assertNull($sub->end_date);
    }

    public function test_it_snapshots_the_otp_mode_from_the_plan(): void
    {
        $customer = $this->makeCustomer();
        $planId = $this->makePlan(['otp_mode' => SubscriptionPlan::OTP_WITH]);

        $this->service()->fulfil($this->makeOrder($customer, $planId), 'pay_1');

        $this->assertSame(
            SubscriptionPlan::OTP_WITH,
            CustomerSubscription::where('customer_id', $customer)->value('otp_mode')
        );
    }

    /** Editing the plan later must not change coverage already sold. */
    public function test_the_snapshot_survives_a_later_plan_edit(): void
    {
        $customer = $this->makeCustomer();
        $planId = $this->makePlan(['otp_mode' => SubscriptionPlan::OTP_WITH]);

        $this->service()->fulfil($this->makeOrder($customer, $planId), 'pay_1');

        DB::table('subscription_plans')->where('id', $planId)
            ->update(['otp_mode' => SubscriptionPlan::OTP_WITHOUT]);

        $this->assertSame(
            SubscriptionPlan::OTP_WITH,
            CustomerSubscription::where('customer_id', $customer)->value('otp_mode')
        );
    }

    public function test_buying_a_plan_deactivates_the_previous_plan(): void
    {
        $customer = $this->makeCustomer();

        $this->service()->fulfil($this->makeOrder($customer, $this->makePlan()), 'pay_1');
        $this->service()->fulfil($this->makeOrder($customer, $this->makePlan(['name' => 'Yearly'])), 'pay_2');

        $this->assertSame(1, CustomerSubscription::where('customer_id', $customer)->where('is_active', 1)->count());
    }

    /**
     * The behaviour renew() got wrong: it deactivated everything, so buying a
     * plan destroyed an unused per-agreement credit the customer had paid for.
     */
    public function test_buying_a_plan_leaves_an_unused_agreement_credit_alone(): void
    {
        $customer = $this->makeCustomer();
        $creditPlan = $this->makePlan(['duration_type' => 'per_agreement', 'price' => 15, 'agreement_limit' => 1]);

        $this->service()->fulfil($this->makeOrder($customer, $creditPlan, 1500), 'pay_1');
        $this->service()->fulfil($this->makeOrder($customer, $this->makePlan()), 'pay_2');

        $credit = CustomerSubscription::where('customer_id', $customer)
            ->where('subscription_plan_id', $creditPlan)->first();

        $this->assertSame(1, (int) $credit->is_active);
        $this->assertSame(1, (int) $credit->remaining_agreements);
    }

    public function test_buying_a_credit_does_not_deactivate_a_running_plan(): void
    {
        $customer = $this->makeCustomer();
        $monthly = $this->makePlan();

        $this->service()->fulfil($this->makeOrder($customer, $monthly), 'pay_1');

        $creditPlan = $this->makePlan(['duration_type' => 'per_agreement', 'price' => 15, 'agreement_limit' => 1]);
        $this->service()->fulfil($this->makeOrder($customer, $creditPlan, 1500), 'pay_2');

        $this->assertSame(
            1,
            (int) CustomerSubscription::where('customer_id', $customer)
                ->where('subscription_plan_id', $monthly)->value('is_active')
        );
    }

    public function test_the_invoice_number_is_unique_across_orders(): void
    {
        $customer = $this->makeCustomer();
        $planId = $this->makePlan();

        $this->service()->fulfil($this->makeOrder($customer, $planId), 'pay_1');
        $this->service()->fulfil($this->makeOrder($customer, $planId), 'pay_2');

        $numbers = SubscriptionInvoice::where('customer_id', $customer)->pluck('invoice_number');
        $this->assertCount(2, $numbers->unique());
    }

    public function test_mark_failed_records_the_reason_and_grants_nothing(): void
    {
        $customer = $this->makeCustomer();
        $order = $this->makeOrder($customer, $this->makePlan());

        $this->service()->markFailed($order, 'BAD_REQUEST_ERROR: card declined');

        $this->assertSame(PaymentOrder::STATUS_FAILED, $order->fresh()->status);
        $this->assertStringContainsString('declined', $order->fresh()->failure_reason);
        $this->assertSame(0, CustomerSubscription::where('customer_id', $customer)->count());
    }

    public function test_a_paid_order_cannot_be_marked_failed(): void
    {
        $order = $this->makeOrder($this->makeCustomer(), $this->makePlan());
        $this->service()->fulfil($order, 'pay_1');

        $this->service()->markFailed($order->fresh(), 'late failure webhook');

        $this->assertSame(PaymentOrder::STATUS_PAID, $order->fresh()->status);
    }
}
