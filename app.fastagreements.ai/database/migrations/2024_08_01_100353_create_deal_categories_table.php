<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('deal_categories', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();
            $table->string('category_name'); 
            $table->string('category_image'); 
            $table->boolean('is_active')->default(true); 
            $table->decimal('deal_price', 8, 2); 
            $table->boolean('is_on_interest')->default(false); 
            $table->text('description')->nullable(); 
            $table->timestamps(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deal_categories');
    }
};
