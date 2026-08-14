<?php

namespace App\Filament\Pages\Dashboards;

use App\Models\Branch;
use App\Models\Client;
use App\Models\Deal;
use App\Models\Employee;
use App\Models\Invoice;
use App\Models\JournalEntry;
use App\Models\CoaBalance;
use Filament\Pages\Page;

class CeoDashboard extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-presentation-chart-line';

    protected static ?int $navigationSort = 5;

    protected string $view = 'filament.pages.dashboards.ceo';

    protected static ?string $title = 'Dashboard Eksekutif';

    protected static ?string $navigationLabel = 'Dashboard Eksekutif';

    protected static ?string $slug = 'ceo-dashboard';

    public static function getNavigationGroup(): ?string
    {
        return "\u{1F3E0} Dashboard";
    }

    public array $revenueCurrent = [];
    public array $revenueLast = [];
    public float $revenueGrowth = 0;
    public float $profitMargin = 0;
    public float $cashBalance = 0;
    public array $arAging = [];
    public array $topCustomers = [];
    public array $branchPerformance = [];
    public int $employeeCount = 0;
    public int $activeProjects = 0;
    public float $totalPipelineValue = 0;
    public array $revenueChartLabels = [];
    public array $revenueChartData = [];

    public function mount(): void
    {
        $companyId = auth()->user()->company_id;
        $now = now();
        $thisMonth = $now->startOfMonth();
        $lastMonth = $now->copy()->subMonth()->startOfMonth();
        $lastMonthEnd = $now->copy()->subMonth()->endOfMonth();

        $this->revenueCurrent = $this->getRevenueSummary($companyId, $thisMonth, $now);
        $this->revenueLast = $this->getRevenueSummary($companyId, $lastMonth, $lastMonthEnd);

        $currentRev = $this->revenueCurrent['total_revenue'] ?? 0;
        $lastRev = $this->revenueLast['total_revenue'] ?? 0;
        if ($lastRev > 0) {
            $this->revenueGrowth = round((($currentRev - $lastRev) / $lastRev) * 100, 1);
        }

        $this->profitMargin = $this->calculateProfitMargin($companyId, $thisMonth, $now);
        $this->cashBalance = $this->getCashBalance($companyId);
        $this->arAging = $this->getArAging($companyId);
        $this->topCustomers = $this->getTopCustomers($companyId, 5);
        $this->branchPerformance = $this->getBranchPerformance($companyId, $thisMonth, $now);
        $this->employeeCount = Employee::where('company_id', $companyId)->where('status', 'active')->count();
        $this->activeProjects = \App\Models\Project::where('company_id', $companyId)
            ->whereIn('status', ['planning', 'active'])->count();
        $this->totalPipelineValue = Deal::where('company_id', $companyId)
            ->whereNotIn('status', ['lost', 'won'])->sum('expected_value');
        $this->revenueChartData = $this->getRevenueChartData($companyId);
    }

    protected function getRevenueSummary(int $companyId, $startDate, $endDate): array
    {
        $paid = Invoice::where('company_id', $companyId)
            ->where('status', 'paid')
            ->whereBetween('invoice_date', [$startDate, $endDate])
            ->selectRaw('COALESCE(SUM(total), 0) as total_revenue, COUNT(*) as invoice_count')
            ->first();

        $unpaid = Invoice::where('company_id', $companyId)
            ->whereIn('status', ['sent', 'overdue'])
            ->whereBetween('invoice_date', [$startDate, $endDate])
            ->selectRaw('COALESCE(SUM(total), 0) as total_outstanding, COUNT(*) as unpaid_count')
            ->first();

        $expenses = JournalEntry::whereHas('journal', function ($q) use ($companyId, $startDate, $endDate) {
            $q->where('company_id', $companyId)
                ->where('status', 'posted')
                ->whereBetween('journal_date', [$startDate, $endDate]);
        })->whereHas('coa', function ($q) {
            $q->whereHas('category', function ($q2) {
                $q2->where('code', 'BEBAN');
            });
        })->sum('debit');

        return [
            'total_revenue' => (float) $paid->total_revenue,
            'invoice_count' => (int) $paid->invoice_count,
            'total_outstanding' => (float) $unpaid->total_outstanding,
            'unpaid_count' => (int) $unpaid->unpaid_count,
            'total_expenses' => (float) $expenses,
        ];
    }

    protected function calculateProfitMargin(int $companyId, $startDate, $endDate): float
    {
        $revenue = Invoice::where('company_id', $companyId)
            ->where('status', 'paid')
            ->whereBetween('invoice_date', [$startDate, $endDate])
            ->sum('total');

        if ($revenue <= 0) return 0;

        $expenses = JournalEntry::whereHas('journal', function ($q) use ($companyId, $startDate, $endDate) {
            $q->where('company_id', $companyId)
                ->where('status', 'posted')
                ->whereBetween('journal_date', [$startDate, $endDate]);
        })->whereHas('coa', function ($q) {
            $q->whereHas('category', function ($q2) {
                $q2->where('code', 'BEBAN');
            });
        })->sum('debit');

        return round((($revenue - $expenses) / $revenue) * 100, 1);
    }

    protected function getCashBalance(int $companyId): float
    {
        $currentMonth = now()->month;
        $currentYear = now()->year;

        return (float) CoaBalance::whereHas('coa', function ($q) {
            $q->whereHas('category', function ($q2) {
                $q2->where('code', 'ASET');
            })->where('code', 'like', '1-1%');
        })->where('year', $currentYear)->where('month', $currentMonth)->sum('closing_balance');
    }

    protected function getArAging(int $companyId): array
    {
        $now = now();

        $overdue = Invoice::where('company_id', $companyId)
            ->whereIn('status', ['sent', 'overdue'])
            ->where('due_date', '<', $now)
            ->sum('remaining_amount');

        $dueThisWeek = Invoice::where('company_id', $companyId)
            ->whereIn('status', ['sent', 'overdue'])
            ->whereBetween('due_date', [$now, $now->copy()->addWeek()])
            ->sum('remaining_amount');

        $totalAR = Invoice::where('company_id', $companyId)
            ->whereIn('status', ['sent', 'overdue'])
            ->sum('remaining_amount');

        return [
            'overdue' => (float) $overdue,
            'due_this_week' => (float) $dueThisWeek,
            'total_ar' => (float) $totalAR,
        ];
    }

    protected function getTopCustomers(int $companyId, int $limit): array
    {
        $top = Invoice::where('company_id', $companyId)
            ->where('status', 'paid')
            ->whereIn('reference_entity', ['Client', 'App\Models\Client'])
            ->selectRaw('reference_id, SUM(total) as revenue, COUNT(*) as invoice_count')
            ->groupBy('reference_id')
            ->orderByDesc('revenue')
            ->limit($limit)
            ->get();

        $clientIds = $top->pluck('reference_id');
        $clients = Client::whereIn('id', $clientIds)->get()->keyBy('id');

        return $top->map(function ($row) use ($clients) {
            $client = $clients->get($row->reference_id);
            return [
                'id' => $row->reference_id,
                'name' => $client?->name ?? 'Client #' . $row->reference_id,
                'revenue' => (float) $row->revenue,
                'invoice_count' => (int) $row->invoice_count,
            ];
        })->toArray();
    }

    protected function getBranchPerformance(int $companyId, $startDate, $endDate): array
    {
        $branches = Branch::where('company_id', $companyId)->where('is_active', true)->get();

        return $branches->map(function ($branch) use ($startDate, $endDate) {
            $revenue = Invoice::where('company_id', $branch->company_id)
                ->where('branch_id', $branch->id)
                ->where('status', 'paid')
                ->whereBetween('invoice_date', [$startDate, $endDate])
                ->sum('total');

            $employeeCount = Employee::where('branch_id', $branch->id)->where('status', 'active')->count();

            return [
                'id' => $branch->id,
                'name' => $branch->name,
                'revenue' => (float) $revenue,
                'employee_count' => $employeeCount,
            ];
        })->toArray();
    }

    protected function getRevenueChartData(int $companyId): array
    {
        $months = collect(range(0, 5))->map(function ($i) {
            $d = now()->subMonths($i);
            return [
                'label' => $d->translatedFormat('M Y'),
                'start' => $d->copy()->startOfMonth(),
                'end' => $d->copy()->endOfMonth(),
            ];
        })->reverse()->values();

        $labels = $months->pluck('label')->toArray();
        $data = $months->map(function ($m) use ($companyId) {
            return (float) Invoice::where('company_id', $companyId)
                ->where('status', 'paid')
                ->whereBetween('invoice_date', [$m['start'], $m['end']])
                ->sum('total');
        })->toArray();

        return ['labels' => $labels, 'data' => $data];
    }
}
