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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('mobile');
            $table->string('email')->nullable();
            $table->text('address');
            $table->unsignedBigInteger('city_id');
            $table->unsignedBigInteger('state_id');
            $table->unsignedBigInteger('country_id');
            $table->text('per_address');
            $table->unsignedBigInteger('per_city_id');
            $table->unsignedBigInteger('per_state_id');
            $table->unsignedBigInteger('per_country_id');
            $table->string('person_image')->nullable();
            $table->string('aadhaar_card')->nullable();
            $table->boolean('is_aadhaar_verify')->default(false);
            $table->json('aadhaar_card_all_column')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_payment_details_configured')->default(false);

            // Bank Details
            $table->string('bank_name');
            $table->string('account_number');
            $table->string('ifsc');
            $table->string('account_type');
            $table->string('upi_id')->nullable();
            $table->string('upi_image')->nullable();

            $table->decimal('last_lat', 10, 7)->nullable();
            $table->decimal('last_lon', 10, 7)->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
