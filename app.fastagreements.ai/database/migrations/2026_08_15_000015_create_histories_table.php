<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agreement_id')->nullable()->constrained('agreements')->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->text('remark')->nullable();
            $table->string('images')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('histories');
    }
};
