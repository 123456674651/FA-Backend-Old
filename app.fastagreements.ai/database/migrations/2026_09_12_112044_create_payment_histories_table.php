<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payment_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('agreement_id')->nullable()->index();
            
            // Gateway Details
            $table->string('gateway', 50)->default('razorpay');
            $table->string('razorpay_order_id')->nullable()->unique();
            $table->string('razorpay_payment_id')->nullable()->index();
            $table->string('razorpay_signature')->nullable();
            
            // Payment Amount & Status
            $table->decimal('amount', 10, 2);
            $table->decimal('fee', 10, 2)->nullable();
            $table->decimal('tax', 10, 2)->nullable();
            $table->string('currency', 10)->default('INR');
            $table->enum('status', ['created', 'paid', 'failed', 'refunded'])->default('created');
            
            // Payment Method Info (UPI, Card, NetBanking, Wallet, etc.)
            $table->string('payment_method')->nullable();
            $table->string('card_network')->nullable();
            $table->string('card_last4', 4)->nullable();
            $table->string('bank')->nullable();
            $table->string('vpa')->nullable();
            $table->string('wallet')->nullable();
            
            // Customer & Purpose Info
            $table->string('purpose')->nullable();
            $table->string('email')->nullable();
            $table->string('contact')->nullable();
            
            // Invoice & Error Info
            $table->string('invoice_number')->nullable()->index();
            $table->string('invoice_pdf')->nullable();
            $table->text('failure_reason')->nullable();
            $table->string('error_code')->nullable();
            
            // Raw Gateway Response for Audit/Debugging
            $table->json('gateway_response')->nullable();

            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['agreement_id', 'status']);
            $table->index('payment_method');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_histories');
    }
};
