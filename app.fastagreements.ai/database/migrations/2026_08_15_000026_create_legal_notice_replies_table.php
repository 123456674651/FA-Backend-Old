<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('legal_notice_replies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('legal_notice_id')->constrained('legal_notices')->cascadeOnDelete();
            $table->foreignId('admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('message')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('legal_notice_replies');
    }
};
