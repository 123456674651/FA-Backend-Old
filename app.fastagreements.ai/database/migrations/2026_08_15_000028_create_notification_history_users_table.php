<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('notification_history_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('notification_history_id')->constrained('notification_histories')->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->text('fcm_token')->nullable();
            $table->string('delivery_status')->nullable();
            $table->text('failure_reason')->nullable();
            $table->text('firebase_response')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('notification_history_users');
    }
};
