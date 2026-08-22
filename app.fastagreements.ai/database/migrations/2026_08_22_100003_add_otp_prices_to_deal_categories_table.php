<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per-tier pricing for a category.
 *
 * Both nullable, with the existing `deal_price` staying as the fallback — the
 * same arrangement the Node service uses, where most agreement types only ever
 * set one flat fee and only a few are priced differently for verified parties.
 * That way this migration changes no existing price.
 */
return new class extends Migration
{
    public function up(): void
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

    public function down(): void
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
};
