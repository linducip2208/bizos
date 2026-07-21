<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_trips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->restrictOnDelete();
            $table->string('destination', 255);
            $table->text('purpose');
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('estimated_cost', 15, 2)->nullable();
            $table->decimal('actual_cost', 15, 2)->nullable();
            $table->enum('status', ['draft', 'submitted', 'approved', 'completed', 'cancelled'])->default('draft');
            $table->timestamps();
        });

        Schema::create('talent_pools', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('talent_pool_candidates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('talent_pool_id')->constrained('talent_pools')->cascadeOnDelete();
            $table->foreignId('candidate_id')->constrained('candidates')->cascadeOnDelete();
            $table->enum('status', ['available', 'contacted', 'interviewed', 'hired', 'rejected'])->default('available');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('okrs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->foreignId('employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->integer('year');
            $table->tinyInteger('quarter');
            $table->enum('type', ['personal', 'team', 'company'])->default('personal');
            $table->decimal('progress_percent', 5, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('okr_key_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('okr_id')->constrained('okrs')->cascadeOnDelete();
            $table->text('description');
            $table->decimal('target_value', 15, 4);
            $table->decimal('current_value', 15, 4)->default(0);
            $table->string('unit', 50)->nullable();
            $table->decimal('weight', 5, 2)->default(0);
            $table->enum('type', ['numeric', 'percentage', 'boolean', 'milestone'])->default('numeric');
            $table->enum('status', ['not_started', 'on_track', 'at_risk', 'behind', 'completed'])->default('not_started');
            $table->timestamps();
        });

        Schema::create('employee_letters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->restrictOnDelete();
            $table->enum('letter_type', ['offer', 'contract', 'promotion', 'transfer', 'warning', 'termination', 'experience']);
            $table->string('letter_number', 100)->nullable();
            $table->string('subject', 255);
            $table->text('content');
            $table->enum('status', ['draft', 'sent', 'acknowledged'])->default('draft');
            $table->foreignId('issued_by')->nullable()->constrained('employees')->nullOnDelete();
            $table->date('issued_at')->nullable();
            $table->timestamps();
        });

        Schema::create('employee_loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->restrictOnDelete();
            $table->enum('loan_type', ['salary_advance', 'emergency', 'education', 'housing']);
            $table->decimal('amount', 15, 2);
            $table->decimal('approved_amount', 15, 2)->nullable();
            $table->decimal('interest_rate', 5, 2)->default(0);
            $table->integer('installment_count');
            $table->decimal('installment_amount', 15, 2)->default(0);
            $table->decimal('remaining_balance', 15, 2)->default(0);
            $table->date('start_date');
            $table->enum('status', ['requested', 'approved', 'rejected', 'active', 'completed'])->default('requested');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('employee_loan_installments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_loan_id')->constrained('employee_loans')->cascadeOnDelete();
            $table->foreignId('payroll_id')->nullable()->constrained('payrolls')->nullOnDelete();
            $table->integer('installment_number');
            $table->decimal('amount', 15, 2);
            $table->enum('status', ['pending', 'paid'])->default('pending');
            $table->date('due_date');
            $table->date('paid_date')->nullable();
            $table->timestamps();
        });

        Schema::create('disciplinaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->restrictOnDelete();
            $table->string('violation_type', 255);
            $table->text('description');
            $table->enum('action_taken', ['verbal_warning', 'written_warning', 'suspension', 'termination']);
            $table->foreignId('issued_by')->nullable()->constrained('employees')->nullOnDelete();
            $table->date('issued_at')->nullable();
            $table->date('effective_date');
            $table->date('expiry_date')->nullable();
            $table->enum('status', ['active', 'resolved', 'appealed'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('disciplinaries');
        Schema::dropIfExists('employee_loan_installments');
        Schema::dropIfExists('employee_loans');
        Schema::dropIfExists('employee_letters');
        Schema::dropIfExists('okr_key_results');
        Schema::dropIfExists('okrs');
        Schema::dropIfExists('talent_pool_candidates');
        Schema::dropIfExists('talent_pools');
        Schema::dropIfExists('business_trips');
    }
};
