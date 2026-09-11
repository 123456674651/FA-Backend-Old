<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Records which Razorpay key pair (test or live) an order was created with,
 * so verification can be checked against the matching secret later.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_orders', function (Blueprint $table) {
            $table->enum('razorpay_mode', ['test', 'live'])->default('live')->after('currency');
        });
    }

    public function down(): void
    {
        Schema::table('payment_orders', function (Blueprint $table) {
            $table->dropColumn('razorpay_mode');
        });
    }
};
