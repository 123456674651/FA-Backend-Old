<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('feed_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('feed_id')->constrained('feeds')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->string('reason')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('feed_reports');
    }
};
