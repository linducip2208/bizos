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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->restrictOnDelete();
            $table->foreignId('shift_id')->nullable()->constrained('shifts')->nullOnDelete();
            $table->date('date');
            $table->timestamp('clock_in')->nullable();
            $table->timestamp('clock_out')->nullable();
            $table->decimal('clock_in_lat', 12, 8)->nullable();
            $table->decimal('clock_in_lng', 12, 8)->nullable();
            $table->decimal('clock_out_lat', 12, 8)->nullable();
            $table->decimal('clock_out_lng', 12, 8)->nullable();
            $table->string('clock_in_photo', 255)->nullable();
            $table->string('clock_out_photo', 255)->nullable();
            $table->string('clock_in_wifi_bssid', 50)->nullable();
            $table->string('clock_out_wifi_bssid', 50)->nullable();
            $table->enum('status', ['present', 'late', 'absent', 'half_day', 'leave', 'holiday', 'weekend'])->default('present');
            $table->integer('late_minutes')->default(0);
            $table->integer('early_departure_minutes')->default(0);
            $table->integer('overtime_minutes')->default(0);
            $table->enum('work_type', ['office', 'wfh', 'wfa', 'field'])->default('office');
            $table->text('notes')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('employees')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
