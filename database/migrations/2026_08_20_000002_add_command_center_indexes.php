<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $indexes = [
        'invoices' => [['company_id', 'branch_id', 'invoice_date', 'status'], 'cc_invoices_scope_period_status'],
        'journals' => [['company_id', 'branch_id', 'journal_date', 'status'], 'cc_journals_scope_period_status'],
        'purchase_orders' => [['company_id', 'branch_id', 'order_date', 'status'], 'cc_purchase_orders_scope_period_status'],
        'sales_orders' => [['company_id', 'branch_id', 'order_date', 'status'], 'cc_sales_orders_scope_period_status'],
        'deals' => [['company_id', 'status', 'actual_close_date'], 'cc_deals_scope_status_date'],
        'projects' => [['company_id', 'department_id', 'status'], 'cc_projects_scope_department_status'],
        'employees' => [['company_id', 'branch_id', 'department_id', 'status'], 'cc_employees_scope_org_status'],
        'approval_requests' => [['company_id', 'status', 'submitted_at'], 'cc_approvals_scope_status_date'],
    ];

    public function up(): void
    {
        foreach ($this->indexes as $table => [$columns, $name]) {
            Schema::table($table, fn (Blueprint $blueprint) => $blueprint->index($columns, $name));
        }
    }

    public function down(): void
    {
        foreach ($this->indexes as $table => [, $name]) {
            Schema::table($table, fn (Blueprint $blueprint) => $blueprint->dropIndex($name));
        }
    }
};
