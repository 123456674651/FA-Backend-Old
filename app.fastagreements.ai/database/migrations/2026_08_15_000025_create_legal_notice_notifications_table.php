<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('legal_notice_notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreignId('legal_notice_id')->constrained('legal_notices')->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->text('message')->nullable();
            $table->boolean('is_read')->default(0);
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('legal_notice_notifications');
    }
};
