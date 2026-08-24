<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The device token push notifications are addressed to.
 *
 * Every consumer of this column already existed — SendPushNotificationJob,
 * PushNotificationApiController, SendNotificationController and the admin
 * customer screen all read `customers.fcm_token` — but the column itself was
 * never migrated. Anything that touched it therefore failed with
 * "Unknown column 'fcm_token' in 'field list'", including
 * `auth/firebase-exchange`, which stores the token as part of signing in and
 * so returned 500 on every real device (an emulator with no FCM token omits
 * the field and slipped through, which is why the failure looked intermittent).
 *
 * Nullable because it is genuinely unknown until the handset reports one, and
 * because signing in must not depend on push being available. `text` rather
 * than `string` as FCM registration tokens already run past 255 characters and
 * have no documented ceiling. Left unindexed on purpose: the notification
 * sender selects this column but never filters or joins on it.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('customers', 'fcm_token')) {
            return;
        }

        Schema::table('customers', function (Blueprint $table) {
            $table->text('fcm_token')->nullable();
        });
    }

    public function down(): void
    {
        if (!Schema::hasColumn('customers', 'fcm_token')) {
            return;
        }

        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('fcm_token');
        });
    }
};
