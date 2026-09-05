<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('legal_notices', function (Blueprint $table) {
            $table->id();
            $table->string('company_name')->nullable();
            $table->decimal('total_amount', 15, 2)->nullable();
            $table->decimal('amount_due', 15, 2)->nullable();
            $table->string('company_person_name')->nullable();
            $table->string('company_person_designation')->nullable();
            $table->text('company_address')->nullable();
            $table->string('my_company_name')->nullable();
            $table->string('my_company_business_nature')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('legal_notices');
    }
};
