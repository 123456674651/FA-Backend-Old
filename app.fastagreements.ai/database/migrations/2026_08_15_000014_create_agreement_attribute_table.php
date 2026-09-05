<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('agreement_attribute', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agreement_id')->constrained('agreements')->cascadeOnDelete();
            $table->foreignId('attribute_id')->nullable()->constrained('category_attributes')->nullOnDelete();
            $table->text('attribute_value')->nullable();
            $table->integer('scores')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('agreement_attribute');
    }
};
