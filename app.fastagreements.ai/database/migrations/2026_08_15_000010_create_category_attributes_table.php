<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('category_attributes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained('deal_categories')->nullOnDelete();
            $table->string('attribute_name');
            $table->string('attribute_code')->nullable();
            $table->text('attribute_values')->nullable();
            $table->string('input_type')->nullable();
            $table->string('default_value')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_required')->default(0);
            $table->boolean('is_active')->default(1);
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('category_attributes');
    }
};
