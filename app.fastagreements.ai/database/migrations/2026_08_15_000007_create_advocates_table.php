<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('advocates', function (Blueprint $table) {
            $table->id();
            $table->string('image')->nullable();
            $table->string('name');
            $table->string('lawyer_type')->nullable();
            $table->boolean('is_verified')->default(0);
            $table->decimal('price', 10, 2)->nullable();
            $table->string('consultation_time')->nullable();
            $table->integer('total_reviews')->default(0);
            $table->string('experience')->nullable();
            $table->text('about')->nullable();
            $table->json('languages_known')->nullable();
            $table->string('video')->nullable();
            $table->string('document')->nullable();
            $table->json('expertise')->nullable();
            $table->json('degree')->nullable();
            $table->string('address')->nullable();
            $table->string('mobile_number')->nullable();
            $table->boolean('status')->default(1);
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('advocates');
    }
};
