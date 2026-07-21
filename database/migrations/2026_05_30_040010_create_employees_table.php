<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('position_id')->nullable()->constrained('positions')->nullOnDelete();
            $table->foreignId('designation_id')->nullable()->constrained('designations')->nullOnDelete();
            $table->foreignId('grade_id')->nullable()->constrained('grades')->nullOnDelete();
            $table->string('employee_code', 50)->unique()->comment('NIP / ID karyawan');
            $table->string('first_name', 100);
            $table->string('last_name', 100)->nullable();
            $table->string('email', 255);
            $table->string('phone', 30)->nullable();
            $table->enum('gender', ['male', 'female', 'other']);
            $table->date('birth_date')->nullable();
            $table->string('birth_place', 100)->nullable();
            $table->string('religion', 50)->nullable();
            $table->enum('marital_status', ['single', 'married', 'divorced', 'widowed'])->nullable();
            $table->string('nationality', 50)->default('Indonesia');
            $table->string('id_number', 50)->nullable()->comment('NIK KTP');
            $table->string('tax_number', 50)->nullable()->comment('NPWP');
            $table->string('bpjs_kesehatan', 50)->nullable();
            $table->string('bpjs_ketenagakerjaan', 50)->nullable();
            $table->text('address')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('province', 100)->nullable();
            $table->string('postal_code', 10)->nullable();
            $table->string('photo', 255)->nullable();
            $table->date('join_date');
            $table->date('contract_start')->nullable();
            $table->date('contract_end')->nullable();
            $table->enum('employee_type', ['permanent', 'contract', 'probation', 'intern', 'freelance', 'part_time']);
            $table->enum('status', ['active', 'inactive', 'terminated', 'resigned', 'retired'])->default('active');
            $table->date('termination_date')->nullable();
            $table->text('termination_reason')->nullable();
            $table->decimal('basic_salary', 20, 2);
            $table->decimal('hourly_rate', 15, 2)->nullable();
            $table->decimal('overtime_rate', 15, 2)->nullable();
            $table->string('bank_name', 100)->nullable();
            $table->string('bank_account_number', 50)->nullable();
            $table->string('bank_account_name', 255)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
