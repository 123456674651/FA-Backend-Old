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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('deal_id');
            $table->string('title');
            $table->unsignedBigInteger('customer_id');
            $table->text('address');
            $table->unsignedBigInteger('city_id');
            $table->unsignedBigInteger('state_id');
            $table->unsignedBigInteger('country_id');
            $table->decimal('total_amount', 10, 2);
            $table->decimal('total_amount_paid', 10, 2);
            $table->boolean('is_paid');
            $table->date('invoice_date');
            $table->string('invoice_number');
            $table->timestamps();

            // Foreign key constraints (optional, depending on your schema)
            $table->foreign('deal_id')->references('id')->on('deals')->onDelete('cascade');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('cascade');
            $table->foreign('city_id')->references('id')->on('cities')->onDelete('cascade');
            $table->foreign('state_id')->references('id')->on('states')->onDelete('cascade');
            $table->foreign('country_id')->references('id')->on('countries')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
