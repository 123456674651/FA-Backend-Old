<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Links a tax invoice back to the payment that produced it. */
return new class extends Migration
{
    public function up(): void
    {
        // hasTable first — see the subscription_plans migration for why.
        if (!Schema::hasTable('subscription_invoices') || Schema::hasColumn('subscription_invoices', 'payment_order_id')) {
            return;
        }

        Schema::table('subscription_invoices', function (Blueprint $table) {
            $table->unsignedBigInteger('payment_order_id')->nullable()->after('customer_subscription_id');
            $table->foreign('payment_order_id')->references('id')->on('payment_orders')->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('subscription_invoices')) {
            return;
        }

        if (Schema::hasColumn('subscription_invoices', 'payment_order_id')) {
            Schema::table('subscription_invoices', function (Blueprint $table) {
                $table->dropForeign(['payment_order_id']);
                $table->dropColumn('payment_order_id');
            });
        }
    }
};
