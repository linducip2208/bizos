<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_id')->constrained('work_orders')->restrictOnDelete();
            $table->foreignId('technician_id')->constrained('employees')->restrictOnDelete();
            $table->date('report_date');
            $table->text('findings')->nullable();
            $table->text('work_performed')->nullable();
            $table->text('recommendations')->nullable();
            $table->longText('customer_signature')->nullable();
            $table->text('customer_feedback')->nullable();
            $table->timestamps();
        });

        Schema::create('service_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->foreignId('technician_id')->constrained('employees')->restrictOnDelete();
            $table->date('date');
            $table->string('shift', 20)->default('morning');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['technician_id', 'date', 'shift'], 'svc_sched_tech_date_shift_unique');
        });

        Schema::create('service_schedule_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_schedule_id')->constrained('service_schedules')->cascadeOnDelete();
            $table->foreignId('work_order_id')->constrained('work_orders')->restrictOnDelete();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('status', 20)->default('scheduled');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_schedule_items');
        Schema::dropIfExists('service_schedules');
        Schema::dropIfExists('service_reports');
    }
};
