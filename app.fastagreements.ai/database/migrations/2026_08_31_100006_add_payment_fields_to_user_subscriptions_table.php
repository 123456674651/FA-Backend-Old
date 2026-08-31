<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The coverage a subscription was sold with, and the payment that bought it.
 *
 * otp_mode is snapshotted from the plan at purchase rather than read live:
 * an admin editing a plan from with_otp to without_otp must not retroactively
 * downgrade what existing subscribers paid for.
 */
return new class extends Migration
{
    public function up(): void
    {
        // This table comes from the dump, not from a migration; skip cleanly
        // rather than throwing if it is somehow absent.
        if (!Schema::hasTable('user_subscriptions')) {
            return;
        }

        Schema::table('user_subscriptions', function (Blueprint $table) {
            if (!Schema::hasColumn('user_subscriptions', 'otp_mode')) {
                $table->enum('otp_mode', ['with_otp', 'without_otp'])->nullable()->after('subscription_plan_id');
            }

            if (!Schema::hasColumn('user_subscriptions', 'payment_order_id')) {
                $table->unsignedBigInteger('payment_order_id')->nullable()->after('otp_mode');
                $table->foreign('payment_order_id')->references('id')->on('payment_orders')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('user_subscriptions')) {
            return;
        }

        Schema::table('user_subscriptions', function (Blueprint $table) {
            if (Schema::hasColumn('user_subscriptions', 'payment_order_id')) {
                $table->dropForeign(['payment_order_id']);
                $table->dropColumn('payment_order_id');
            }

            if (Schema::hasColumn('user_subscriptions', 'otp_mode')) {
                $table->dropColumn('otp_mode');
            }
        });
    }
};
