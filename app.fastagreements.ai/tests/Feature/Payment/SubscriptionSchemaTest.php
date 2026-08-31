<?php

namespace Tests\Feature\Payment;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SubscriptionSchemaTest extends TestCase
{
    use DatabaseTransactions;

    public function test_payment_orders_table_exists_with_expected_columns(): void
    {
        $this->assertTrue(Schema::hasTable('payment_orders'));
        $this->assertTrue(Schema::hasColumns('payment_orders', [
            'id', 'customer_id', 'subscription_plan_id', 'status',
            'amount_paise', 'currency', 'razorpay_order_id', 'razorpay_payment_id',
            'razorpay_signature', 'failure_reason', 'fulfilled_at', 'expires_at',
            'created_at', 'updated_at',
        ]));
    }

    public function test_razorpay_order_id_is_unique(): void
    {
        $indexes = collect(Schema::getIndexes('payment_orders'));

        $this->assertTrue(
            $indexes->contains(fn ($i) => $i['columns'] === ['razorpay_order_id'] && $i['unique']),
            'razorpay_order_id must be unique so a gateway order maps to exactly one local order.'
        );
    }

    public function test_the_dump_supplied_subscription_tables_are_present(): void
    {
        $this->assertTrue(Schema::hasColumns('subscription_plans', [
            'id', 'name', 'price', 'duration_type', 'duration_value',
            'agreement_limit', 'is_active',
        ]));

        $this->assertTrue(Schema::hasColumns('user_subscriptions', [
            'id', 'customer_id', 'subscription_plan_id', 'start_date',
            'end_date', 'is_active', 'remaining_agreements',
        ]));

        $this->assertTrue(Schema::hasColumns('subscription_invoices', [
            'id', 'customer_id', 'subscription_plan_id', 'customer_subscription_id',
            'invoice_number', 'amount', 'invoice_date', 'payment_status', 'payment_method',
        ]));
    }
}
