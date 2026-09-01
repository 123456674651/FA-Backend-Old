<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('subscription_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignId('subscription_plan_id')->nullable()->constrained('subscription_plans')->nullOnDelete();
            $table->foreignId('customer_subscription_id')->nullable()->constrained('user_subscriptions')->nullOnDelete();
            $table->unsignedBigInteger('agreement_category_id')->nullable();
            $table->unsignedBigInteger('agreement_sub_category_id')->nullable();
            $table->string('agreement_category_name')->nullable();
            $table->string('agreement_sub_category_name')->nullable();
            $table->foreignId('agreement_id')->nullable()->constrained('agreements')->nullOnDelete();
            $table->string('invoice_number')->nullable();
            $table->decimal('amount', 15, 2)->nullable();
            $table->dateTime('invoice_date')->nullable();
            $table->string('payment_status')->nullable();
            $table->string('payment_method')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('subscription_invoices');
    }
};
