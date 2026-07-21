<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('workflows', 'bpmn_xml')) {
            Schema::table('workflows', function (Blueprint $table) {
                $table->string('workflow_type', 30)->default('simple')->change();
                $table->longText('bpmn_xml')->nullable()->after('actions');
                $table->longText('bpmn_svg')->nullable()->after('bpmn_xml');
                $table->json('approval_levels')->nullable()->after('bpmn_svg');
                $table->integer('sla_hours')->nullable()->after('approval_levels');
                $table->string('module', 100)->nullable()->after('sla_hours');
                $table->integer('min_approvers')->default(1)->after('module');
                $table->string('category', 100)->nullable()->after('min_approvers');
            });
        }

        if (!Schema::hasColumn('approval_requests', 'unified_workflow_id')) {
            Schema::table('approval_requests', function (Blueprint $table) {
                $table->foreignId('unified_workflow_id')->nullable()->after('workflow_id')
                    ->constrained('workflows')->nullOnDelete();
            });
        }

        if (!Schema::hasColumn('bpmn_process_instances', 'unified_workflow_id')) {
            Schema::table('bpmn_process_instances', function (Blueprint $table) {
                $table->foreignId('unified_workflow_id')->nullable()->after('process_id')
                    ->constrained('workflows')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::table('workflows', function (Blueprint $table) {
            $table->dropColumn([
                'bpmn_xml', 'bpmn_svg', 'approval_levels',
                'sla_hours', 'module', 'min_approvers', 'category',
            ]);
        });

        Schema::table('approval_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('unified_workflow_id');
        });

        Schema::table('bpmn_process_instances', function (Blueprint $table) {
            $table->dropConstrainedForeignId('unified_workflow_id');
        });
    }
};
