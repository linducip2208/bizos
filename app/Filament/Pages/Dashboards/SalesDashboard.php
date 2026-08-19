<?php

namespace App\Filament\Pages\Dashboards;

use App\Models\Client;
use App\Models\Deal;
use App\Models\Employee;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\PipelineStage;
use App\Models\SalesOrder;
use Filament\Pages\Page;

/** @deprecated Use CommandCenter with the matching tab; the legacy URL is preserved by redirect middleware. */
class SalesDashboard extends Page
{
    protected static bool $shouldRegisterNavigation = false;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?int $navigationSort = 8;

    protected string $view = 'filament.pages.dashboards.sales';

    protected static ?string $title = 'Dashboard Penjualan';

    protected static ?string $navigationLabel = 'Dashboard Penjualan';

    protected static ?string $slug = 'sales-dashboard';

    public static function getNavigationGroup(): ?string
    {
        return \App\Filament\Navigation\NavigationGroup::DASHBOARD->value;
    }

    public float $pipelineValue = 0;
    public int $pipelineCount = 0;
    public float $dealsWonThisMonth = 0;
    public int $dealsWonCount = 0;
    public float $conversionRate = 0;
    public float $revenueThisMonth = 0;
    public float $revenueTarget = 0;
    public float $revenueProgress = 0;
    public array $topSalespeople = [];
    public array $recentWonDeals = [];
    public array $pipelineStages = [];
    public array $pipelineChartLabels = [];
    public array $pipelineChartData = [];
    public array $revenueChartLabels = [];
    public array $revenueChartData = [];
    public int $newLeadsThisMonth = 0;
    public int $activeDeals = 0;

    public function mount(): void
    {
        $companyId = auth()->user()->company_id;
        $now = now();
        $monthStart = $now->copy()->startOfMonth();
        $monthEnd = $now->copy()->endOfMonth();

        $this->loadPipelineData($companyId);
        $this->loadWonDeals($companyId, $monthStart, $monthEnd);
        $this->loadConversionRate($companyId, $monthStart, $monthEnd);
        $this->loadRevenueData($companyId, $monthStart, $monthEnd);
        $this->topSalespeople = $this->getTopSalespeople($companyId, $monthStart, $monthEnd);
        $this->recentWonDeals = $this->getRecentWonDeals($companyId, 5);
        $this->pipelineStages = $this->getPipelineStages($companyId);
        $this->loadChartData($companyId);
        $this->newLeadsThisMonth = Lead::where('company_id', $companyId)
            ->whereBetween('created_at', [$monthStart, $monthEnd])->count();
        $this->activeDeals = Deal::where('company_id', $companyId)
            ->whereNotIn('status', ['lost', 'won'])->count();
    }

    protected function loadPipelineData(int $companyId): void
    {
        $this->pipelineValue = (float) Deal::where('company_id', $companyId)
            ->whereNotIn('status', ['lost', 'won'])
            ->sum('expected_value');

        $this->pipelineCount = Deal::where('company_id', $companyId)
            ->whereNotIn('status', ['lost', 'won'])
            ->count();
    }

    protected function loadWonDeals(int $companyId, $start, $end): void
    {
        $this->dealsWonThisMonth = (float) Deal::where('company_id', $companyId)
            ->where('status', 'won')
            ->whereBetween('actual_close_date', [$start, $end])
            ->sum('expected_value');

        $this->dealsWonCount = Deal::where('company_id', $companyId)
            ->where('status', 'won')
            ->whereBetween('actual_close_date', [$start, $end])
            ->count();
    }

    protected function loadConversionRate(int $companyId, $start, $end): void
    {
        $total = Deal::where('company_id', $companyId)
            ->whereBetween('created_at', [$start, $end])
            ->count();

        $won = Deal::where('company_id', $companyId)
            ->where('status', 'won')
            ->whereBetween('actual_close_date', [$start, $end])
            ->count();

        $this->conversionRate = $total > 0 ? round(($won / $total) * 100, 1) : 0;
    }

    protected function loadRevenueData(int $companyId, $start, $end): void
    {
        $this->revenueThisMonth = (float) Invoice::where('company_id', $companyId)
            ->where('status', 'paid')
            ->whereBetween('invoice_date', [$start, $end])
            ->sum('total');

        $this->revenueTarget = (float) Deal::where('company_id', $companyId)
            ->whereMonth('expected_close_date', now()->month)
            ->whereYear('expected_close_date', now()->year)
            ->sum('expected_value');

        $this->revenueProgress = $this->revenueTarget > 0
            ? round(($this->revenueThisMonth / $this->revenueTarget) * 100, 1)
            : 0;
    }

    protected function getTopSalespeople(int $companyId, $start, $end): array
    {
        $top = Deal::where('company_id', $companyId)
            ->where('status', 'won')
            ->whereBetween('actual_close_date', [$start, $end])
            ->whereNotNull('assigned_to')
            ->selectRaw('assigned_to, COUNT(*) as won_deals, SUM(expected_value) as won_value')
            ->groupBy('assigned_to')
            ->orderByDesc('won_value')
            ->limit(5)
            ->get();

        $employeeIds = $top->pluck('assigned_to');
        $employees = Employee::whereIn('id', $employeeIds)->with('position')->get()->keyBy('id');

        return $top->map(function ($row) use ($employees) {
            $emp = $employees->get($row->assigned_to);
            return [
                'id' => $row->assigned_to,
                'name' => $emp ? $emp->first_name . ' ' . $emp->last_name : 'Sales #' . $row->assigned_to,
                'position' => $emp?->position?->name ?? '-',
                'won_deals' => (int) $row->won_deals,
                'won_value' => (float) $row->won_value,
            ];
        })->toArray();
    }

    protected function getRecentWonDeals(int $companyId, int $limit): array
    {
        return Deal::with(['client', 'assignedTo'])
            ->where('company_id', $companyId)
            ->where('status', 'won')
            ->latest('actual_close_date')
            ->limit($limit)
            ->get()
            ->map(fn ($d) => [
                'id' => $d->id,
                'title' => $d->title,
                'client_name' => $d->client?->name ?? 'N/A',
                'salesperson' => $d->assignedTo?->first_name . ' ' . $d->assignedTo?->last_name,
                'value' => (float) $d->expected_value,
                'closed_date' => $d->actual_close_date?->format('d M Y'),
            ])
            ->toArray();
    }

    protected function getPipelineStages(int $companyId): array
    {
        return PipelineStage::where('company_id', $companyId)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->withCount(['deals' => function ($q) {
                $q->whereNotIn('status', ['lost', 'won']);
            }])
            ->withSum(['deals' => function ($q) {
                $q->whereNotIn('status', ['lost', 'won']);
            }], 'expected_value')
            ->get()
            ->map(fn ($s) => [
                'id' => $s->id,
                'name' => $s->name,
                'color' => $s->color,
                'deals_count' => $s->deals_count,
                'deals_value' => (float) $s->deals_sum_expected_value,
            ])
            ->toArray();
    }

    protected function loadChartData(int $companyId): void
    {
        $months = collect(range(0, 5))->map(function ($i) {
            $d = now()->subMonths($i);
            return [
                'label' => $d->translatedFormat('M'),
                'start' => $d->copy()->startOfMonth(),
                'end' => $d->copy()->endOfMonth(),
            ];
        })->reverse()->values();

        $this->pipelineChartLabels = $months->pluck('label')->toArray();
        $this->pipelineChartData = $months->map(function ($m) use ($companyId) {
            return (float) Deal::where('company_id', $companyId)
                ->whereNotIn('status', ['lost', 'won'])
                ->whereBetween('created_at', [$m['start'], $m['end']])
                ->sum('expected_value');
        })->toArray();

        $this->revenueChartLabels = $months->pluck('label')->toArray();
        $this->revenueChartData = $months->map(function ($m) use ($companyId) {
            return (float) Invoice::where('company_id', $companyId)
                ->where('status', 'paid')
                ->whereBetween('invoice_date', [$m['start'], $m['end']])
                ->sum('total');
        })->toArray();
    }
}
