<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->string('category')->default('custom');
            $table->enum('query_type', ['table', 'chart', 'summary'])->default('table');
            $table->json('query_config')->nullable();
            $table->json('chart_config')->nullable();
            $table->boolean('is_system')->default(false);
            $table->boolean('is_public')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['company_id', 'slug']);
            $table->index(['company_id', 'category']);
        });

        Schema::create('report_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_template_id')->constrained('report_templates')->cascadeOnDelete();
            $table->string('name');
            $table->json('recipients')->nullable();
            $table->enum('frequency', ['daily', 'weekly', 'monthly'])->default('daily');
            $table->time('time_of_day')->default('08:00:00');
            $table->unsignedTinyInteger('day_of_week')->nullable();
            $table->unsignedTinyInteger('day_of_month')->nullable();
            $table->enum('format', ['pdf', 'excel', 'csv'])->default('pdf');
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_sent_at')->nullable();
            $table->timestamps();
        });

        Schema::create('report_snapshots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_template_id')->constrained('report_templates')->cascadeOnDelete();
            $table->foreignId('generated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('snapshot_data')->nullable();
            $table->string('format')->default('pdf');
            $table->string('file_path')->nullable();
            $table->unsignedBigInteger('file_size')->default(0);
            $table->timestamp('created_at')->nullable();

            $table->index('report_template_id');
        });

        Schema::create('dashboard_widgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('widget_type')->default('stats');
            $table->string('title');
            $table->json('config')->nullable();
            $table->json('position')->nullable();
            $table->boolean('is_pinned')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['company_id', 'user_id']);
        });

        Schema::create('dashboard_layouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->json('layout_config')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dashboard_layouts');
        Schema::dropIfExists('dashboard_widgets');
        Schema::dropIfExists('report_snapshots');
        Schema::dropIfExists('report_schedules');
        Schema::dropIfExists('report_templates');
    }
};
