<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Proof that a phone number was confirmed, recorded before any agreement exists.
 *
 * `create_aggriment` builds the agreement and renders the document in a single
 * call, so there is no moment between "the agreement exists" and "the document
 * is produced" in which parties could be asked to confirm. Verification
 * therefore has to happen first, against the number itself.
 *
 * Scoped to the customer who is assembling the agreement so one person's
 * confirmations cannot be spent by another, and consumed only while fresh —
 * see AgreementOtpModeService::VERIFICATION_TTL_MINUTES.
 *
 * agreement_party_verifications is the permanent record; rows here are copied
 * into it at creation time.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('party_phone_verifications')) {
            return;
        }

        Schema::create('party_phone_verifications', function (Blueprint $table) {
            $table->id();

            // The customer assembling the agreement, not the person confirming.
            $table->unsignedBigInteger('customer_id');

            // Ten digits, matching customers.mobile.
            $table->string('mobile', 15);

            $table->string('firebase_uid', 128)->nullable();

            $table->timestamp('verified_at');

            $table->timestamps();

            // One live row per number per customer — re-confirming refreshes
            // the timestamp rather than accumulating history.
            $table->unique(['customer_id', 'mobile'], 'ppv_customer_mobile_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('party_phone_verifications');
    }
};
