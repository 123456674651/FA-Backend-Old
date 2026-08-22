<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Per-person verification state for an agreement.
 *
 * A side table rather than columns on `agreements`, because guarantors are
 * stored there as comma-joined strings (`guarantor`, `guarantor_number`) with
 * nowhere to hang a timestamp. Keeping this separate means PhpWordController
 * and every .docx template keep reading exactly what they read today —
 * verification is purely additive.
 *
 * No foreign key to `agreements`: that table was created by hand rather than
 * by a migration, so its storage engine and key types are not guaranteed to
 * support one. The index carries the lookup; the application enforces the link.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('agreement_party_verifications')) {
            return;
        }

        Schema::create('agreement_party_verifications', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('agreement_id');

            // Which person on the agreement this row is about.
            $table->enum('role', ['party_1', 'party_2', 'guarantor']);

            // Guarantors are positional (0-3, matching the exploded string).
            // Always 0 for party_1 and party_2.
            $table->unsignedTinyInteger('position')->default(0);

            // Snapshotted at verification time. Kept even if the agreement's
            // guarantor list is later edited, so an audit can tell which number
            // actually confirmed.
            $table->string('mobile', 15);

            $table->timestamp('verified_at')->nullable();

            // Firebase uid of the account that confirmed. Stable per phone
            // number within the project, so a repeated verification is
            // recognisable as the same handset.
            $table->string('firebase_uid', 128)->nullable();

            $table->enum('verified_via', ['firebase', 'none'])->nullable();

            $table->timestamps();

            $table->unique(['agreement_id', 'role', 'position'], 'apv_agreement_role_position_unique');
            $table->index('agreement_id', 'apv_agreement_id_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agreement_party_verifications');
    }
};
