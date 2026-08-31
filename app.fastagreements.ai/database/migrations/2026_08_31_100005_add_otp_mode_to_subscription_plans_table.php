<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Which verification tier a plan sells.
 *
 * Nullable, and NULL means "covers both modes". Every existing plan row has
 * no answer, and picking one for them would either block subscribers from
 * OTP agreements they already paid for, or hand out OTP coverage nobody
 * bought. NULL asserts nothing.
 */
return new class extends Migration
{
    public function up(): void
    {
        // hasTable first: this table comes from the dump rather than from a
        // migration, and hasColumn() throws on a table that is not there.
        if (!Schema::hasTable('subscription_plans') || Schema::hasColumn('subscription_plans', 'otp_mode')) {
            return;
        }

        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->enum('otp_mode', ['with_otp', 'without_otp'])->nullable()->after('duration_value');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('subscription_plans')) {
            return;
        }

        if (Schema::hasColumn('subscription_plans', 'otp_mode')) {
            Schema::table('subscription_plans', function (Blueprint $table) {
                $table->dropColumn('otp_mode');
            });
        }
    }
};
