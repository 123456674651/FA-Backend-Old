<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Which verification tier the customer chose for this agreement.
 *
 * Nullable because every agreement created before this change has no answer,
 * and guessing one either way would be wrong: treating old rows as `with_otp`
 * would block them from ever generating a document, and treating them as
 * `without_otp` would assert a choice the customer never made. Null means
 * "predates the feature" and is handled as no verification requirement.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('agreements') || Schema::hasColumn('agreements', 'otp_mode')) {
            return;
        }

        Schema::table('agreements', function (Blueprint $table) {
            $table->enum('otp_mode', ['with_otp', 'without_otp'])->nullable()->after('id');
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('agreements') && Schema::hasColumn('agreements', 'otp_mode')) {
            Schema::table('agreements', function (Blueprint $table) {
                $table->dropColumn('otp_mode');
            });
        }
    }
};
