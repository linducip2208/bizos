<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approval_workflows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('name');
            $table->string('module');
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('min_approvers')->default(1);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'module']);
        });

        Schema::create('approval_levels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workflow_id')->constrained('approval_workflows')->cascadeOnDelete();
            $table->unsignedInteger('level');
            $table->enum('approver_type', ['role', 'employee', 'department', 'position']);
            $table->unsignedBigInteger('approver_id')->nullable();
            $table->boolean('is_required')->default(true);
            $table->boolean('can_delegate')->default(false);
            $table->unsignedInteger('sla_hours')->nullable();
            $table->enum('sla_action', ['remind', 'escalate', 'auto_approve', 'auto_reject'])->nullable();
            $table->timestamps();
        });

        Schema::create('approval_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('workflow_id')->constrained('approval_workflows')->restrictOnDelete();
            $table->string('module');
            $table->unsignedBigInteger('module_id');
            $table->string('title');
            $table->foreignId('requester_id')->constrained('employees')->restrictOnDelete();
            $table->enum('status', ['pending', 'approved', 'rejected', 'cancelled'])->default('pending');
            $table->unsignedInteger('current_level')->default(1);
            $table->unsignedInteger('total_levels');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['module', 'module_id']);
            $table->index(['company_id', 'status']);
            $table->index('requester_id');
        });

        Schema::create('approval_actions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('approval_request_id')->constrained('approval_requests')->cascadeOnDelete();
            $table->foreignId('level_id')->constrained('approval_levels')->restrictOnDelete();
            $table->foreignId('approver_id')->constrained('employees')->restrictOnDelete();
            $table->enum('action', ['approve', 'reject', 'delegate']);
            $table->foreignId('delegated_to')->nullable()->constrained('employees')->restrictOnDelete();
            $table->text('comment')->nullable();
            $table->timestamp('action_at');
            $table->timestamps();
        });

        Schema::create('approval_delegations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('approver_id')->constrained('employees')->restrictOnDelete();
            $table->foreignId('delegate_id')->constrained('employees')->restrictOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['approver_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_delegations');
        Schema::dropIfExists('approval_actions');
        Schema::dropIfExists('approval_requests');
        Schema::dropIfExists('approval_levels');
        Schema::dropIfExists('approval_workflows');
    }
};
