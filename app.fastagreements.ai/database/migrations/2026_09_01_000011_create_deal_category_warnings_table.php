<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('deal_category_warnings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('deal_category_id')->nullable()->constrained('deal_categories')->nullOnDelete();
            $table->foreignId('language_id')->nullable()->constrained('languages')->nullOnDelete();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->integer('display_order')->default(0);
            $table->boolean('status')->default(1);
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('deal_category_warnings');
    }
};
