<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->foreignId('payroll_period_id')->nullable()->after('approved_at')
                ->constrained('payroll_periods')->nullOnDelete();
        });

        Schema::table('timesheet_entries', function (Blueprint $table) {
            $table->foreignId('invoice_id')->nullable()->after('is_billable')
                ->constrained('invoices')->nullOnDelete();
            $table->boolean('is_billed')->default(false)->after('invoice_id');
        });

        Schema::table('milestones', function (Blueprint $table) {
            $table->foreignId('invoice_id')->nullable()->after('status')
                ->constrained('invoices')->nullOnDelete();
        });

        Schema::table('employee_documents', function (Blueprint $table) {
            $table->enum('verification_status', ['pending', 'verified', 'rejected'])
                ->default('pending')->after('notes');
            $table->foreignId('verified_by')->nullable()->after('verification_status')
                ->constrained('employees')->nullOnDelete();
            $table->timestamp('verified_at')->nullable()->after('verified_by');
            $table->text('rejection_reason')->nullable()->after('verified_at');
        });

        Schema::table('family_members', function (Blueprint $table) {
            $table->string('nik', 30)->nullable()->after('is_dependent');
            $table->string('kk_number', 30)->nullable()->after('nik');
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropForeign(['payroll_period_id']);
            $table->dropColumn('payroll_period_id');
        });

        Schema::table('timesheet_entries', function (Blueprint $table) {
            $table->dropForeign(['invoice_id']);
            $table->dropColumn(['invoice_id', 'is_billed']);
        });

        Schema::table('milestones', function (Blueprint $table) {
            $table->dropForeign(['invoice_id']);
            $table->dropColumn('invoice_id');
        });

        Schema::table('employee_documents', function (Blueprint $table) {
            $table->dropForeign(['verified_by']);
            $table->dropColumn(['verification_status', 'verified_by', 'verified_at', 'rejection_reason']);
        });

        Schema::table('family_members', function (Blueprint $table) {
            $table->dropColumn(['nik', 'kk_number']);
        });
    }
};
