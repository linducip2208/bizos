<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table) {
            if (!Schema::hasColumn('tickets', 'satisfaction_comment')) {
                $table->text('satisfaction_comment')->nullable()->after('satisfaction_rating');
            }
            if (!Schema::hasColumn('tickets', 'survey_sent_at')) {
                $table->timestamp('survey_sent_at')->nullable()->after('satisfaction_comment');
            }
        });

        Schema::create('sla_business_hours', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->foreignId('sla_policy_id')->nullable()->constrained('sla_policies')->cascadeOnDelete();
            $table->string('day_of_week', 10);
            $table->time('start_time');
            $table->time('end_time');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('sla_holidays', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->restrictOnDelete();
            $table->string('name', 255);
            $table->date('holiday_date');
            $table->boolean('is_recurring_yearly')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('employees')->nullOnDelete();
            $table->timestamps();

            $table->unique(['company_id', 'holiday_date']);
        });

        Schema::create('sla_escalation_levels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sla_policy_id')->constrained('sla_policies')->cascadeOnDelete();
            $table->integer('level')->default(1);
            $table->integer('minutes_before_breach');
            $table->text('action_description')->nullable();
            $table->json('notify_employee_ids')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sla_escalation_levels');
        Schema::dropIfExists('sla_holidays');
        Schema::dropIfExists('sla_business_hours');
    }
};
