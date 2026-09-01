<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('category_languages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained('deal_categories')->nullOnDelete();
            $table->foreignId('language_id')->nullable()->constrained('languages')->nullOnDelete();
            $table->longText('agreement_text')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('category_languages');
    }
};
