<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Adds the profile fields the V2 mobile registration endpoint collects,
     * so newly registered accounts can be stored in `users` instead of
     * `customers`.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('mobile')->nullable()->unique()->after('email');
            $table->string('address')->nullable()->after('mobile');
            $table->boolean('is_company')->default(false)->after('address');
            $table->string('company_name')->nullable()->after('is_company');
            $table->string('gst_number')->nullable()->after('company_name');
            $table->string('location')->nullable()->after('gst_number');
            $table->text('signature')->nullable()->after('location');
            $table->string('occupation')->nullable()->after('signature');
            $table->date('date_of_birth')->nullable()->after('occupation');
            $table->string('gender')->nullable()->after('date_of_birth');
            $table->string('photo')->nullable()->after('gender');
        });

        // Mobile-OTP registration provides neither an email nor a password
        // (identity is proven via OTP, not a password broker), so these can
        // no longer stay NOT NULL/unique-by-default like the base
        // Breeze/Jetstream scaffold assumed.
        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable()->change();
            $table->string('password')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'mobile',
                'address',
                'is_company',
                'company_name',
                'gst_number',
                'location',
                'signature',
                'occupation',
                'date_of_birth',
                'gender',
                'photo',
            ]);
        });
    }
};
