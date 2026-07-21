<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bpmn_processes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->string('name', 500);
            $table->string('category', 100)->nullable();
            $table->text('description')->nullable();
            $table->longText('bpmn_xml')->nullable()->comment('BPMN 2.0 XML definition');
            $table->longText('diagram_svg')->nullable();
            $table->boolean('is_prebuilt')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('version')->default(1);
            $table->string('sla_hours', 50)->nullable()->comment('Overall SLA in hours');
            $table->timestamps();
        });

        Schema::create('bpmn_process_instances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('process_id')->constrained('bpmn_processes')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->string('instance_code', 100);
            $table->enum('status', ['running', 'completed', 'terminated', 'suspended', 'error'])->default('running');
            $table->string('current_element_id', 255)->nullable()->comment('Current active BPMN element ID');
            $table->string('current_element_name', 500)->nullable();
            $table->json('process_variables')->nullable()->comment('Snapshot of process variables');
            $table->foreignId('started_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->string('error_message', 1000)->nullable();
            $table->timestamps();

            $table->unique(['process_id', 'instance_code']);
        });

        Schema::create('bpmn_task_instances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('process_instance_id')->constrained('bpmn_process_instances')->cascadeOnDelete();
            $table->string('element_id', 255)->comment('BPMN element ID from XML');
            $table->string('task_name', 500);
            $table->enum('type', ['user_task', 'service_task', 'script_task', 'send_task', 'receive_task', 'manual_task', 'business_rule_task'])->default('user_task');
            $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled', 'error'])->default('pending');
            $table->enum('gateway_type', ['exclusive', 'inclusive', 'parallel', 'event_based', 'complex'])->nullable();
            $table->string('gateway_default_flow', 255)->nullable();
            $table->foreignId('assignee_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('assignee_role', 100)->nullable();
            $table->json('input_variables')->nullable();
            $table->json('output_variables')->nullable();
            $table->decimal('sla_hours', 8, 2)->nullable();
            $table->integer('priority')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('sla_deadline')->nullable();
            $table->string('next_element_id', 255)->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
        });

        Schema::create('bpmn_process_variables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('process_instance_id')->constrained('bpmn_process_instances')->cascadeOnDelete();
            $table->string('variable_name', 255);
            $table->text('variable_value')->nullable();
            $table->string('variable_type', 50)->default('string')->comment('string / integer / float / boolean / json / array');
            $table->timestamps();

            $table->unique(['process_instance_id', 'variable_name']);
        });

        Schema::create('bpmn_execution_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('process_instance_id')->constrained('bpmn_process_instances')->cascadeOnDelete();
            $table->string('element_id', 255)->nullable();
            $table->string('element_name', 500)->nullable();
            $table->string('event_type', 50)->comment('process_started / task_started / task_completed / gateway_evaluated / process_completed / process_error');
            $table->json('event_data')->nullable();
            $table->foreignId('actor_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('duration_seconds', 10, 2)->nullable();
            $table->timestamp('logged_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bpmn_execution_logs');
        Schema::dropIfExists('bpmn_process_variables');
        Schema::dropIfExists('bpmn_task_instances');
        Schema::dropIfExists('bpmn_process_instances');
        Schema::dropIfExists('bpmn_processes');
    }
};
