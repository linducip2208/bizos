<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('iso_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('audit_number')->unique();
            $table->string('title');
            $table->enum('audit_type', ['internal', 'external', 'surveillance', 'certification', 'recertification']);
            $table->string('scope');
            $table->text('criteria')->nullable();
            $table->string('auditor_name')->nullable();
            $table->string('auditor_external')->nullable(); // nama auditor eksternal / badan sertifikasi
            $table->date('planned_date');
            $table->date('actual_date')->nullable();
            $table->date('completed_date')->nullable();
            $table->enum('status', ['planned', 'in_progress', 'completed', 'cancelled'])->default('planned');
            $table->enum('result', ['pass', 'pass_with_observation', 'fail', 'pending'])->nullable();
            $table->text('summary')->nullable();
            $table->text('conclusion')->nullable();
            $table->foreignId('lead_auditor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('iso_audits');
    }
};
