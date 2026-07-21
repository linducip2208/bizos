<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('radiologies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->foreignId('patient_id')->constrained('patients')->restrictOnDelete();
            $table->foreignId('doctor_id')->constrained('employees')->restrictOnDelete();
            $table->date('order_date');
            $table->string('radiology_type', 30)->default('xray');
            $table->string('body_part')->nullable();
            $table->text('findings')->nullable();
            $table->text('impression')->nullable();
            $table->string('status', 20)->default('ordered');
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('insurances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->foreignId('patient_id')->constrained('patients')->restrictOnDelete();
            $table->string('insurance_provider');
            $table->string('policy_number', 100);
            $table->string('coverage_type', 50)->nullable();
            $table->decimal('coverage_limit', 15, 2)->default(0);
            $table->date('expiry_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('insurances');
        Schema::dropIfExists('radiologies');
    }
};
