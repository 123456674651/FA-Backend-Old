<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('aadhar_respose', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('aadhaar_number')->nullable();
            $table->string('status')->nullable();
            $table->text('message')->nullable();
            $table->string('email')->nullable();
            $table->string('care_of')->nullable();
            $table->string('name')->nullable();
            $table->string('year_of_birth')->nullable();
            $table->string('gender')->nullable();
            $table->string('ref_id')->nullable();
            $table->string('mobile_hash')->nullable();
            $table->text('address')->nullable();
            $table->string('dob')->nullable();
            $table->text('photo_link')->nullable();
            $table->string('house')->nullable();
            $table->string('landmark')->nullable();
            $table->string('pincode')->nullable();
            $table->string('po')->nullable();
            $table->string('state')->nullable();
            $table->string('street')->nullable();
            $table->string('subdist')->nullable();
            $table->string('vtc')->nullable();
            $table->string('country')->nullable();
            $table->string('dist')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('aadhar_respose');
    }
};
