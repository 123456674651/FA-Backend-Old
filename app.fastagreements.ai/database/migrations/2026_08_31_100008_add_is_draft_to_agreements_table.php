<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('agreements') || Schema::hasColumn('agreements', 'is_draft')) {
            return;
        }

        Schema::table('agreements', function (Blueprint $table) {
            $table->boolean('is_draft')->default(false)->after('otp_mode');
        });
    }

    public function down(): void
    {
        if (Schema::hasTable('agreements') && Schema::hasColumn('agreements', 'is_draft')) {
            Schema::table('agreements', function (Blueprint $table) {
                $table->dropColumn('is_draft');
            });
        }
    }
};
