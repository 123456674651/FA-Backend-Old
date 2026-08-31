<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The record of an intent to pay, created before the customer ever reaches
 * Razorpay.
 *
 * Existing before the money moves is the whole point: it is what makes the
 * amount server-authoritative, what a returning signature is checked against,
 * and what gives a failed or abandoned payment somewhere to be recorded.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('subscription_plan_id');
            $table->enum('status', ['created', 'paid', 'failed', 'expired'])->default('created');

            // Integer paise, matching Razorpay's own unit. Money as a float
            // would round, and these figures end up on tax invoices.
            $table->unsignedBigInteger('amount_paise');
            $table->char('currency', 3)->default('INR');

            $table->string('razorpay_order_id')->nullable()->unique();
            $table->string('razorpay_payment_id')->nullable()->index();
            $table->string('razorpay_signature')->nullable();

            $table->string('failure_reason')->nullable();
            $table->timestamp('fulfilled_at')->nullable();
            // useCurrent() is not the intended expiry semantics — the app
            // always supplies an explicit expires_at on insert. It exists
            // only because this MariaDB (10.4, explicit_defaults_for_timestamp=0)
            // rejects a NOT NULL timestamp column with no default at all
            // (error 1067), so a valid default is required even though it
            // should never actually be relied upon.
            $table->timestamp('expires_at')->useCurrent();
            $table->timestamps();

            $table->index(['customer_id', 'status']);

            $table->foreign('customer_id')->references('id')->on('customers')->cascadeOnDelete();
            $table->foreign('subscription_plan_id')->references('id')->on('subscription_plans');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_orders');
    }
};
