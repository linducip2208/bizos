<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kpi_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->foreignId('position_id')->nullable()->constrained('positions')->nullOnDelete()->comment('Template per jabatan');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('kpi_indicators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained('kpi_templates')->cascadeOnDelete();
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->enum('category', ['financial', 'customer', 'internal_process', 'learning_growth'])->comment('Balanced Scorecard perspective');
            $table->decimal('weight_percent', 5, 2)->comment('Bobot dalam persen');
            $table->enum('target_type', ['numeric', 'percentage', 'boolean', 'rating_1_5']);
            $table->decimal('target_value', 15, 4)->nullable();
            $table->string('measurement_unit', 50)->nullable()->comment('Satuan (Rp, %, unit, dll)');
            $table->string('data_source', 255)->nullable()->comment('Sumber data pengukuran');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('performance_cycles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->string('name', 255);
            $table->date('period_start');
            $table->date('period_end');
            $table->enum('status', ['draft', 'active', 'review', 'completed'])->default('draft');
            $table->timestamps();
        });

        Schema::create('performance_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cycle_id')->constrained('performance_cycles')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('reviewer_id')->constrained('employees')->restrictOnDelete()->comment('Manager/atasan yang menilai');
            $table->foreignId('kpi_template_id')->constrained('kpi_templates')->restrictOnDelete();
            $table->decimal('employee_self_score', 5, 2)->nullable();
            $table->decimal('reviewer_score', 5, 2)->nullable();
            $table->decimal('calibration_score', 5, 2)->nullable()->comment('Skor setelah kalibrasi HR');
            $table->decimal('final_score', 5, 2)->nullable();
            $table->enum('status', ['self_assessment', 'manager_review', 'hr_calibration', 'completed'])->default('self_assessment');
            $table->timestamp('self_submitted_at')->nullable();
            $table->timestamp('review_submitted_at')->nullable();
            $table->timestamp('calibration_at')->nullable();
            $table->timestamps();
        });

        Schema::create('performance_review_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('review_id')->constrained('performance_reviews')->cascadeOnDelete();
            $table->foreignId('indicator_id')->constrained('kpi_indicators')->restrictOnDelete();
            $table->decimal('weight', 5, 2);
            $table->decimal('employee_score', 5, 2)->nullable();
            $table->decimal('reviewer_score', 5, 2)->nullable();
            $table->decimal('calibration_score', 5, 2)->nullable();
            $table->text('comments')->nullable();
            $table->timestamps();
        });

        Schema::create('performance_feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignId('review_id')->constrained('performance_reviews')->cascadeOnDelete();
            $table->foreignId('from_employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('to_employee_id')->constrained('employees')->cascadeOnDelete();
            $table->enum('feedback_type', ['peer', 'subordinate', 'manager', 'self']);
            $table->tinyInteger('rating')->comment('Skala 1-5');
            $table->text('strengths')->nullable()->comment('Kelebihan/keunggulan');
            $table->text('improvements')->nullable()->comment('Area yang perlu ditingkatkan');
            $table->boolean('is_anonymous')->default(false);
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('performance_feedback');
        Schema::dropIfExists('performance_review_scores');
        Schema::dropIfExists('performance_reviews');
        Schema::dropIfExists('performance_cycles');
        Schema::dropIfExists('kpi_indicators');
        Schema::dropIfExists('kpi_templates');
    }
};
