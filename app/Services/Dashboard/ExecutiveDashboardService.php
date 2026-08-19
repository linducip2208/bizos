<?php

namespace App\Services\Dashboard;

use App\Models\ApprovalRequest;
use App\Models\Invoice;
use App\Models\Journal;
use App\Models\Project;
use App\Models\PurchaseOrder;
use App\Models\StockBalance;
use App\Models\Task;
use App\Models\User;

final class ExecutiveDashboardService extends AbstractDashboardService
{
    protected function build(DashboardFilter $filter, User $user): array
    {
        [$previousFrom, $previousTo] = $filter->previousPeriod();
        $invoices = $this->tenant(Invoice::query(), $filter);
        $revenue = (float) (clone $invoices)->where('status', 'paid')->whereBetween('invoice_date', [$filter->dateFrom, $filter->dateTo])->sum('total');
        $previousRevenue = (float) (clone $invoices)->where('status', 'paid')->whereBetween('invoice_date', [$previousFrom, $previousTo])->sum('total');
        $expense = (float) $this->tenant(Journal::query(), $filter)->where('status', 'posted')->whereIn('journal_type', ['purchase', 'cash_payment'])->whereBetween('journal_date', [$filter->dateFrom, $filter->dateTo])->sum('total_debit');
        $cash = (float) $this->tenant(Journal::query(), $filter)->where('status', 'posted')->whereIn('journal_type', ['cash_receipt', 'cash_payment'])->sum('total_debit');
        $ar = (float) (clone $invoices)->whereIn('status', ['sent', 'overdue'])->sum('remaining_amount');
        $ap = (float) $this->tenant(PurchaseOrder::query(), $filter)->whereIn('status', ['approved', 'partially_received', 'received'])->sum('total');
        $approvals = $this->tenant(ApprovalRequest::query(), $filter, false)->where('status', 'pending')->count();
        $projects = $this->tenant(Project::query(), $filter, false)
            ->when($filter->projectId, fn ($query) => $query->whereKey($filter->projectId));
        $projectIds = (clone $projects)->pluck('id');
        $revenueTrend = (clone $invoices)->where('status', 'paid')
            ->whereBetween('invoice_date', [$filter->dateFrom, $filter->dateTo])
            ->orderBy('invoice_date')->get(['invoice_date', 'total'])
            ->groupBy(fn (Invoice $invoice) => $invoice->invoice_date->format('Y-m-d'))
            ->map(fn ($rows, $date) => ['period' => $date, 'revenue' => (float) $rows->sum('total')])->values()->all();
        $expenseTrend = $this->tenant(Journal::query(), $filter)->where('status', 'posted')
            ->whereIn('journal_type', ['purchase', 'cash_payment'])
            ->whereBetween('journal_date', [$filter->dateFrom, $filter->dateTo])
            ->orderBy('journal_date')->get(['journal_date', 'total_debit'])
            ->groupBy(fn (Journal $journal) => $journal->journal_date->format('Y-m-d'))
            ->map(fn ($rows, $date) => ['period' => $date, 'expense' => (float) $rows->sum('total_debit')])->values()->all();

        return [
            'kpis' => [
                ['key' => 'revenue', 'label' => 'Revenue', 'value' => $revenue, 'delta' => $this->delta($revenue, $previousRevenue), 'format' => 'currency', 'url' => '/admin/invoices'],
                ['key' => 'profit', 'label' => 'Net Profit', 'value' => $revenue - $expense, 'delta' => null, 'format' => 'currency', 'url' => '/admin/business-report'],
                ['key' => 'cash', 'label' => 'Cash Balance', 'value' => $cash, 'delta' => null, 'format' => 'currency', 'url' => '/admin/cash-flow-dashboard'],
                ['key' => 'ar', 'label' => 'Outstanding AR', 'value' => $ar, 'delta' => null, 'format' => 'currency', 'url' => '/admin/invoices'],
                ['key' => 'ap', 'label' => 'Outstanding AP', 'value' => $ap, 'delta' => null, 'format' => 'currency', 'url' => '/admin/purchase-orders'],
                ['key' => 'approvals', 'label' => 'Pending Approvals', 'value' => $approvals, 'delta' => null, 'format' => 'number', 'url' => '/admin/approval-center'],
            ],
            'charts' => [
                'revenue_expense' => ['revenue' => $revenueTrend, 'expense' => $expenseTrend],
                'budget_actual' => [
                    'budget' => (float) (clone $projects)->sum('budget'),
                    'actual' => (float) (clone $projects)->sum('actual_cost'),
                ],
            ],
            'signals' => [
                ['label' => 'Pipeline Penjualan', 'value' => (float) \App\Models\Deal::query()->where('company_id', $filter->companyId)->whereNotIn('status', ['won', 'lost'])->sum('expected_value'), 'format' => 'currency'],
                ['label' => 'Peringatan Inventori', 'value' => StockBalance::query()->where('stock_balances.company_id', $filter->companyId)->join('products', 'products.id', '=', 'stock_balances.product_id')->whereColumn('stock_balances.quantity', '<=', 'products.min_stock')->count(), 'format' => 'number'],
                ['label' => 'Proyek Bermasalah', 'value' => (clone $projects)->where('status', 'active')->whereColumn('actual_cost', '>', 'budget')->count(), 'format' => 'number'],
                ['label' => 'Tugas Terlambat', 'value' => Task::query()->whereIn('project_id', $projectIds)->whereNotIn('status', ['done', 'completed'])->whereDate('due_date', '<', today())->count(), 'format' => 'number'],
            ],
            'last_updated' => now()->toIso8601String(),
        ];
    }
}
