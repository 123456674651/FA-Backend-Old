<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->decimal('price', 10, 2)->default(0);
            $table->string('duration_type')->default('monthly'); // daily, monthly, yearly, lifetime, per_agreement
            $table->integer('duration_value')->nullable();
            $table->json('features')->nullable();
            $table->boolean('is_active')->default(1);
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('subscription_plans');
    }
};
