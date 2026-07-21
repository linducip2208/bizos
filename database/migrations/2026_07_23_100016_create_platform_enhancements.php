<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plugins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('name');
            $table->string('version')->default('1.0.0');
            $table->string('author')->nullable();
            $table->text('description')->nullable();
            $table->enum('status', ['installed', 'active', 'inactive', 'error'])->default('installed');
            $table->json('config')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'name']);
        });

        Schema::create('feature_flags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_enabled')->default(false);
            $table->timestamp('enabled_at')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'name']);
        });

        Schema::create('job_monitors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('job_id')->nullable();
            $table->string('job_name');
            $table->enum('status', ['pending', 'running', 'completed', 'failed'])->default('pending');
            $table->unsignedTinyInteger('progress_percent')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'status']);
            $table->index('job_id');
        });

        Schema::create('queue_monitors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('queue_name');
            $table->unsignedInteger('pending_count')->default(0);
            $table->unsignedInteger('processing_count')->default(0);
            $table->unsignedInteger('failed_count')->default(0);
            $table->timestamp('checked_at')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'queue_name']);
        });

        Schema::create('system_health_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('check_type');
            $table->enum('status', ['ok', 'warning', 'error'])->default('ok');
            $table->json('details')->nullable();
            $table->timestamp('checked_at')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'check_type']);
        });

        Schema::create('system_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('level', 20)->default('info');
            $table->string('channel', 50)->default('app');
            $table->text('message');
            $table->json('context')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['company_id', 'level']);
            $table->index('channel');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_logs');
        Schema::dropIfExists('system_health_checks');
        Schema::dropIfExists('queue_monitors');
        Schema::dropIfExists('job_monitors');
        Schema::dropIfExists('feature_flags');
        Schema::dropIfExists('plugins');
    }
};
