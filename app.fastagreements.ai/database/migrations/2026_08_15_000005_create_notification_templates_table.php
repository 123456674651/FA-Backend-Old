<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('notification_templates', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('notification_type')->nullable();
            $table->string('subject')->nullable();
            $table->text('message')->nullable();
            $table->string('image')->nullable();
            $table->boolean('status')->default(1);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('notification_templates');
    }
};
