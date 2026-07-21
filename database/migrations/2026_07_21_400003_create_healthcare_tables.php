<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // patients
        Schema::create('patients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->string('patient_number', 30)->unique();
            $table->string('first_name', 100);
            $table->string('last_name', 100)->nullable();
            $table->enum('gender', ['male', 'female', 'other']);
            $table->date('birth_date')->nullable();
            $table->string('birth_place', 100)->nullable();
            $table->string('religion', 50)->nullable();
            $table->enum('blood_type', ['A', 'B', 'AB', 'O', 'unknown'])->default('unknown');
            $table->string('nik', 16)->nullable();
            $table->string('bpjs_number', 50)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('province', 100)->nullable();
            $table->string('emergency_contact_name', 100)->nullable();
            $table->string('emergency_contact_phone', 30)->nullable();
            $table->json('allergies')->nullable();
            $table->text('medical_history_notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->date('registered_at');
            $table->timestamps();
            $table->softDeletes();
        });

        // appointments
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->foreignId('patient_id')->constrained('patients')->restrictOnDelete();
            $table->foreignId('doctor_id')->constrained('employees')->restrictOnDelete();
            $table->date('appointment_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->enum('appointment_type', ['consultation', 'treatment', 'checkup', 'vaccination']);
            $table->enum('status', ['scheduled', 'confirmed', 'arrived', 'in_progress', 'completed', 'cancelled', 'no_show'])
                ->default('scheduled');
            $table->text('notes')->nullable();
            $table->integer('queue_number')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // medical_records (SOAP)
        Schema::create('medical_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->restrictOnDelete();
            $table->foreignId('doctor_id')->constrained('employees')->restrictOnDelete();
            $table->foreignId('appointment_id')->nullable()->constrained('appointments')->nullOnDelete();
            $table->date('visit_date');
            $table->text('subjective');
            $table->text('objective');
            $table->json('vital_signs')->nullable();
            $table->text('assessment');
            $table->text('plan');
            $table->string('diagnosis_code', 20)->nullable();
            $table->string('diagnosis_name')->nullable();
            $table->boolean('is_final')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });

        // prescriptions
        Schema::create('prescriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medical_record_id')->constrained('medical_records')->restrictOnDelete();
            $table->foreignId('patient_id')->constrained('patients')->restrictOnDelete();
            $table->foreignId('doctor_id')->constrained('employees')->restrictOnDelete();
            $table->date('prescription_date');
            $table->enum('status', ['draft', 'dispensed', 'cancelled'])->default('draft');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // prescription_items
        Schema::create('prescription_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prescription_id')->constrained('prescriptions')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->string('dosage')->nullable();
            $table->string('frequency')->nullable();
            $table->integer('duration_days')->nullable();
            $table->decimal('quantity', 10, 2);
            $table->text('instructions')->nullable();
            $table->timestamps();
        });

        // bpjs_claims
        Schema::create('bpjs_claims', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->foreignId('patient_id')->constrained('patients')->restrictOnDelete();
            $table->foreignId('medical_record_id')->constrained('medical_records')->restrictOnDelete();
            $table->string('claim_number', 50)->unique();
            $table->string('sep_number', 50)->nullable();
            $table->string('ina_cbgs_code', 20)->nullable();
            $table->string('ina_cbgs_description')->nullable();
            $table->decimal('claim_amount', 20, 2)->default(0);
            $table->decimal('approved_amount', 20, 2)->nullable();
            $table->enum('status', ['draft', 'submitted', 'pending', 'approved', 'rejected', 'paid'])
                ->default('draft');
            $table->dateTime('submitted_at')->nullable();
            $table->dateTime('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // lab_orders
        Schema::create('lab_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->foreignId('patient_id')->constrained('patients')->restrictOnDelete();
            $table->foreignId('doctor_id')->constrained('employees')->restrictOnDelete();
            $table->foreignId('medical_record_id')->nullable()->constrained('medical_records')->nullOnDelete();
            $table->date('order_date');
            $table->enum('lab_type', ['hematology', 'chemistry', 'microbiology', 'radiology', 'urine', 'other']);
            $table->text('notes')->nullable();
            $table->enum('status', ['ordered', 'sample_collected', 'in_progress', 'completed', 'reviewed'])
                ->default('ordered');
            $table->timestamps();
            $table->softDeletes();
        });

        // lab_results
        Schema::create('lab_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lab_order_id')->constrained('lab_orders')->cascadeOnDelete();
            $table->string('test_name');
            $table->string('result_value')->nullable();
            $table->string('unit', 50)->nullable();
            $table->string('normal_range')->nullable();
            $table->boolean('is_abnormal')->default(false);
            $table->text('notes')->nullable();
            $table->string('performed_by')->nullable();
            $table->dateTime('performed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lab_results');
        Schema::dropIfExists('lab_orders');
        Schema::dropIfExists('bpjs_claims');
        Schema::dropIfExists('prescription_items');
        Schema::dropIfExists('prescriptions');
        Schema::dropIfExists('medical_records');
        Schema::dropIfExists('appointments');
        Schema::dropIfExists('patients');
    }
};
