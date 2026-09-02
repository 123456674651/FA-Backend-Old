<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Removes per-category OTP pricing, which was never wired up.
 *
 * These columns were added for a design where each agreement category carried
 * its own with-OTP and without-OTP prices. The product decision went the other
 * way: `otp_mode` now lives on the subscription plan, so one pair of prices
 * applies to every category and `PaymentOrderService` resolves the plan.
 *
 * Nothing ever read them — `AgreementOtpModeService::resolvePrice()` was their
 * only consumer and it had zero callers, so it goes too. Leaving two plausible
 * price sources in the schema is how the next person prices an agreement from
 * the wrong one.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('deal_categories')) {
            return;
        }

        Schema::table('deal_categories', function (Blueprint $table) {
            foreach (['price_with_otp', 'price_without_otp'] as $column) {
                if (Schema::hasColumn('deal_categories', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('deal_categories')) {
            return;
        }

        Schema::table('deal_categories', function (Blueprint $table) {
            if (!Schema::hasColumn('deal_categories', 'price_with_otp')) {
                $table->decimal('price_with_otp', 10, 2)->nullable()->after('deal_price');
            }

            if (!Schema::hasColumn('deal_categories', 'price_without_otp')) {
                $table->decimal('price_without_otp', 10, 2)->nullable()->after('price_with_otp');
            }
        });
    }
};
