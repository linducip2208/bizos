<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('succession_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('position_id')->constrained('positions')->cascadeOnDelete();
            $table->foreignId('current_incumbent_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->foreignId('successor_employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->string('readiness')->default('2_years')->comment('ready_now, 1_year, 2_years, 3_plus_years');
            $table->string('risk_level')->default('medium')->comment('high, medium, low');
            $table->text('notes')->nullable();
            $table->json('development_plan')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('succession_plans');
    }
};
