<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('onboarding_checklists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        Schema::create('onboarding_checklist_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('checklist_id')->constrained('onboarding_checklists')->cascadeOnDelete();
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->enum('assigned_role', ['hr', 'it', 'finance', 'manager', 'employee']);
            $table->integer('sort_order')->default(0);
            $table->boolean('is_required')->default(true);
            $table->integer('days_before_join')->default(0)->comment('Hari sebelum join date untuk trigger task ini');
            $table->timestamps();
        });

        Schema::create('onboarding_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('checklist_id')->constrained('onboarding_checklists')->restrictOnDelete();
            $table->date('started_at')->nullable();
            $table->date('completed_at')->nullable();
            $table->enum('status', ['pending', 'in_progress', 'completed'])->default('pending');
            $table->timestamps();
        });

        Schema::create('onboarding_progress_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('progress_id')->constrained('onboarding_progress')->cascadeOnDelete();
            $table->foreignId('checklist_item_id')->constrained('onboarding_checklist_items')->restrictOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('employees')->nullOnDelete();
            $table->date('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'in_progress', 'completed', 'skipped'])->default('pending');
            $table->timestamps();
        });

        Schema::create('offboarding_checklists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        Schema::create('offboarding_checklist_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('checklist_id')->constrained('offboarding_checklists')->cascadeOnDelete();
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->enum('assigned_role', ['hr', 'it', 'finance', 'manager', 'employee']);
            $table->integer('sort_order')->default(0);
            $table->boolean('is_required')->default(true);
            $table->timestamps();
        });

        Schema::create('offboarding_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('checklist_id')->constrained('offboarding_checklists')->restrictOnDelete();
            $table->date('resignation_date');
            $table->date('last_working_date');
            $table->decimal('final_settlement_amount', 20, 2)->nullable();
            $table->enum('clearance_status', ['pending', 'it_clear', 'finance_clear', 'hr_clear', 'asset_clear', 'completed'])->default('pending');
            $table->text('exit_interview_notes')->nullable();
            $table->timestamps();
        });

        Schema::create('offboarding_progress_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('progress_id')->constrained('offboarding_progress')->cascadeOnDelete();
            $table->foreignId('checklist_item_id')->constrained('offboarding_checklist_items')->restrictOnDelete();
            $table->foreignId('completed_by')->nullable()->constrained('employees')->nullOnDelete();
            $table->date('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'in_progress', 'completed', 'skipped'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('offboarding_progress_items');
        Schema::dropIfExists('offboarding_progress');
        Schema::dropIfExists('offboarding_checklist_items');
        Schema::dropIfExists('offboarding_checklists');
        Schema::dropIfExists('onboarding_progress_items');
        Schema::dropIfExists('onboarding_progress');
        Schema::dropIfExists('onboarding_checklist_items');
        Schema::dropIfExists('onboarding_checklists');
    }
};
