<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Records which verification a row came from, now that the provider is MSG91
 * rather than Firebase.
 *
 * `firebase_uid` is deliberately left in place. Rows written before this
 * migration really were verified through Firebase, and that uid is the only
 * evidence of it; overwriting or dropping it would falsify a record the
 * agreement document depends on. New rows populate `provider_ref` instead and
 * leave `firebase_uid` null, so the source of any row is unambiguous.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('party_phone_verifications')
            && !Schema::hasColumn('party_phone_verifications', 'provider_ref')) {
            Schema::table('party_phone_verifications', function (Blueprint $table) {
                $table->string('provider_ref')->nullable()->after('firebase_uid');
                $table->index('provider_ref');
            });
        }

        if (Schema::hasTable('agreement_party_verifications')
            && !Schema::hasColumn('agreement_party_verifications', 'provider_ref')) {
            Schema::table('agreement_party_verifications', function (Blueprint $table) {
                $table->string('provider_ref')->nullable()->after('firebase_uid');
            });
        }

        // The replay guard's own storage.
        //
        // Deliberately NOT a lookup over party_phone_verifications: that table
        // is keyed on (customer_id, mobile) and updated in place, so a second
        // confirmation of the same number overwrites the first one's reference
        // and silently makes the earlier token replayable again. A guard that
        // forgets is worse than none — it reads as protection. This table only
        // ever grows, which is exactly what "already used" requires.
        if (!Schema::hasTable('used_phone_tokens')) {
            Schema::create('used_phone_tokens', function (Blueprint $table) {
                $table->id();
                $table->string('provider_ref')->unique();
                $table->timestamp('used_at');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('used_phone_tokens');

        if (Schema::hasColumn('party_phone_verifications', 'provider_ref')) {
            Schema::table('party_phone_verifications', function (Blueprint $table) {
                $table->dropIndex(['provider_ref']);
                $table->dropColumn('provider_ref');
            });
        }

        if (Schema::hasColumn('agreement_party_verifications', 'provider_ref')) {
            Schema::table('agreement_party_verifications', function (Blueprint $table) {
                $table->dropColumn('provider_ref');
            });
        }
    }
};
