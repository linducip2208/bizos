<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('period_id')->constrained('payroll_periods')->restrictOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->restrictOnDelete();
            $table->decimal('gross_salary', 20, 2);
            $table->decimal('total_income_components', 20, 2)->default(0);
            $table->decimal('total_deduction_components', 20, 2)->default(0);
            $table->decimal('pph21_amount', 20, 2)->default(0);
            $table->decimal('bpjs_tk_jht', 20, 2)->default(0);
            $table->decimal('bpjs_tk_jp', 20, 2)->default(0);
            $table->decimal('bpjs_tk_jkk', 20, 2)->default(0);
            $table->decimal('bpjs_tk_jkm', 20, 2)->default(0);
            $table->decimal('bpjs_kes', 20, 2)->default(0);
            $table->decimal('net_salary', 20, 2);
            $table->integer('attendance_days')->default(0);
            $table->integer('leave_days')->default(0);
            $table->decimal('overtime_hours', 8, 2)->default(0);
            $table->decimal('overtime_pay', 20, 2)->default(0);
            $table->enum('status', ['draft', 'finalized', 'paid'])->default('draft');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};
