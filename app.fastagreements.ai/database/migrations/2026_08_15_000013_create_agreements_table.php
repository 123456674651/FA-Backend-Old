<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('agreements', function (Blueprint $table) {
            $table->id();
            $table->enum('otp_mode', ['with_otp', 'without_otp'])->nullable();

            $table->foreignId('party_1_id')->constrained('customers')->cascadeOnDelete();
            $table->longText('party_1_signature')->nullable();
            $table->foreignId('party_2_id')->constrained('customers')->cascadeOnDelete();
            $table->longText('party_2_signature')->nullable();

            $table->decimal('amount', 15, 2)->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->date('agreement_date')->nullable();
            $table->string('agreement_type')->nullable();
            $table->boolean('is_interest')->default(0);
            $table->string('reference_no')->nullable();
            $table->text('reference_remark')->nullable();
            $table->text('address')->nullable();
            $table->text('note')->nullable();
            $table->decimal('security', 15, 2)->nullable();
            $table->string('guarantor')->nullable();
            $table->string('guarantor_number')->nullable();
            $table->string('agreement_status')->nullable();
            $table->integer('period')->nullable();
            $table->string('documents')->nullable();
            $table->string('repayment_term')->nullable();

            $table->foreignId('aggriment_language_id')->nullable()->constrained('languages')->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('deal_categories')->nullOnDelete();
            $table->unsignedBigInteger('sub_category')->nullable();
            $table->string('location')->nullable();
            $table->string('purpose')->nullable();

            $table->string('party_1_image')->nullable();
            $table->string('party_2_image')->nullable();
            $table->string('party_1_adhar_front')->nullable();
            $table->string('party_1_adhar_back')->nullable();
            $table->string('party_2_adhar_front')->nullable();
            $table->string('party_2_adhar_back')->nullable();

            // No FK constraint: subscription_invoices.agreement_id points back here,
            // which would be a circular dependency between the two tables.
            $table->unsignedBigInteger('invoice_id')->nullable();

            $table->string('vehicle_front_side')->nullable();
            $table->string('vehicle_back_side')->nullable();
            $table->string('vehicle_left_side')->nullable();
            $table->string('vehicle_right_side')->nullable();

            $table->integer('party_1_age')->nullable();
            $table->integer('party_2_age')->nullable();
            $table->string('party_1_business')->nullable();
            $table->string('party_2_business')->nullable();

            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('agreements');
    }
};
