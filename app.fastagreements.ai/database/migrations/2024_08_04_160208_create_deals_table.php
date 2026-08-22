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
        Schema::create('deals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('person_1');
            $table->unsignedBigInteger('person_2');
            $table->decimal('amount', 15, 2);
            $table->string('purpose');
            $table->string('video_recording')->nullable();
            $table->string('audio_recording')->nullable();
            $table->string('images')->nullable();
            $table->unsignedBigInteger('category_id');
            $table->unsignedBigInteger('language_id');
            $table->unsignedBigInteger('contract_template_file_name_id');
            $table->date('from_date');
            $table->date('to_date');
            $table->date('contract_date');
            $table->string('status');
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lon', 10, 7)->nullable();
            $table->boolean('allow_live_location')->default(false);
            $table->decimal('interest_rate', 5, 2)->nullable();
            $table->decimal('payable_amount', 15, 2)->nullable();
            $table->integer('interest_term_in_month')->nullable();
            $table->string('sakshi')->nullable();
            $table->unsignedBigInteger('mediator_id')->nullable();
            $table->string('currency');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deals');
    }
};
