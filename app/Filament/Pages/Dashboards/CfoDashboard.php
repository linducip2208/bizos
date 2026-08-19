<?php

namespace App\Filament\Pages\Dashboards;

use App\Models\Budget;
use App\Models\CoaBalance;
use App\Models\Invoice;
use App\Models\JournalEntry;
use App\Models\Payment;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;

/** @deprecated Use CommandCenter with the matching tab; the legacy URL is preserved by redirect middleware. */
class CfoDashboard extends Page
{
    protected static bool $shouldRegisterNavigation = false;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';

    protected static ?int $navigationSort = 6;

    protected string $view = 'filament.pages.dashboards.cfo';

    protected static ?string $title = 'Dashboard Keuangan';

    protected static ?string $navigationLabel = 'Dashboard Keuangan';

    protected static ?string $slug = 'cfo-dashboard';

    public static function getNavigationGroup(): ?string
    {
        return \App\Filament\Navigation\NavigationGroup::DASHBOARD->value;
    }

    public float $revenueThisMonth = 0;
    public float $budgetPlanned = 0;
    public float $budgetActual = 0;
    public float $budgetVariance = 0;
    public array $expenseBreakdown = [];
    public float $cashInflow = 0;
    public float $cashOutflow = 0;
    public float $netCashflow = 0;
    public float $totalAR = 0;
    public float $totalAP = 0;
    public float $plRevenue = 0;
    public float $plExpenses = 0;
    public float $plNetProfit = 0;
    public array $bankBalances = [];
    public array $cashflowChartLabels = [];
    public array $cashflowChartData = [];

    public function mount(): void
    {
        $companyId = auth()->user()->company_id;
        $now = now();
        $monthStart = $now->copy()->startOfMonth();
        $monthEnd = $now->copy()->endOfMonth();
        $year = $now->year;

        $this->revenueThisMonth = $this->getRevenueThisMonth($companyId, $monthStart, $monthEnd);
        $this->loadBudgetData($companyId, $year);
        $this->expenseBreakdown = $this->getExpenseBreakdown($companyId, $monthStart, $monthEnd);
        $this->loadCashflowData($companyId, $monthStart, $monthEnd);
        $this->totalAR = $this->getTotalAR($companyId);
        $this->totalAP = $this->getTotalAP($companyId);
        $this->loadPnL($companyId, $monthStart, $monthEnd);
        $this->bankBalances = $this->getBankBalances($companyId);
        $this->loadCashflowChart($companyId);
    }

    protected function getRevenueThisMonth(int $companyId, $start, $end): float
    {
        return (float) Invoice::where('company_id', $companyId)
            ->where('status', 'paid')
            ->whereBetween('invoice_date', [$start, $end])
            ->sum('total');
    }

    protected function loadBudgetData(int $companyId, int $year): void
    {
        $budget = Budget::where('company_id', $companyId)
            ->where('fiscal_year', $year)
            ->where('status', 'approved')
            ->first();

        if ($budget) {
            $this->budgetPlanned = (float) $budget->budgetItems()->sum('planned_amount');
            $this->budgetActual = (float) $budget->budgetItems()->sum('actual_amount');
        }

        $ytdExpenses = JournalEntry::whereHas('journal', function ($q) use ($companyId, $year) {
            $q->where('company_id', $companyId)
                ->where('status', 'posted')
                ->whereYear('journal_date', $year);
        })->whereHas('coa', function ($q) {
            $q->whereHas('category', function ($q2) {
                $q2->where('code', 'BEBAN');
            });
        })->sum('debit');

        $this->budgetVariance = $this->budgetPlanned - $ytdExpenses;
    }

    protected function getExpenseBreakdown(int $companyId, $start, $end): array
    {
        return DB::table('journal_entries')
            ->join('journals', 'journal_entries.journal_id', '=', 'journals.id')
            ->join('coa', 'journal_entries.coa_id', '=', 'coa.id')
            ->join('coa_categories', 'coa.category_id', '=', 'coa_categories.id')
            ->where('journals.company_id', $companyId)
            ->where('journals.status', 'posted')
            ->whereBetween('journals.journal_date', [$start, $end])
            ->where('coa_categories.code', 'BEBAN')
            ->selectRaw('coa.name as category, coa.code as code, SUM(journal_entries.debit) as total')
            ->groupBy('coa.name', 'coa.code')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($r) => [
                'category' => $r->category,
                'code' => $r->code,
                'total' => (float) $r->total,
            ])
            ->toArray();
    }

    protected function loadCashflowData(int $companyId, $start, $end): void
    {
        $this->cashInflow = (float) Payment::where('company_id', $companyId)
            ->where('status', 'confirmed')
            ->whereBetween('payment_date', [$start, $end])
            ->sum('amount');

        $this->cashOutflow = (float) JournalEntry::whereHas('journal', function ($q) use ($companyId, $start, $end) {
            $q->where('company_id', $companyId)
                ->where('status', 'posted')
                ->whereBetween('journal_date', [$start, $end]);
        })->whereHas('coa', function ($q) {
            $q->whereHas('category', function ($q2) {
                $q2->where('code', 'BEBAN');
            });
        })->sum('debit');

        $this->netCashflow = $this->cashInflow - $this->cashOutflow;
    }

    protected function getTotalAR(int $companyId): float
    {
        return (float) Invoice::where('company_id', $companyId)
            ->whereIn('status', ['sent', 'overdue'])
            ->sum('remaining_amount');
    }

    protected function getTotalAP(int $companyId): float
    {
        return (float) Invoice::where('company_id', $companyId)
            ->where('invoice_type', 'purchase')
            ->whereIn('status', ['sent', 'partial', 'overdue'])
            ->sum('remaining_amount');
    }

    protected function loadPnL(int $companyId, $start, $end): void
    {
        $this->plRevenue = (float) Invoice::where('company_id', $companyId)
            ->where('status', 'paid')
            ->whereBetween('invoice_date', [$start, $end])
            ->sum('total');

        $this->plExpenses = (float) JournalEntry::whereHas('journal', function ($q) use ($companyId, $start, $end) {
            $q->where('company_id', $companyId)
                ->where('status', 'posted')
                ->whereBetween('journal_date', [$start, $end]);
        })->whereHas('coa', function ($q) {
            $q->whereHas('category', function ($q2) {
                $q2->where('code', 'BEBAN');
            });
        })->sum('debit');

        $this->plNetProfit = $this->plRevenue - $this->plExpenses;
    }

    protected function getBankBalances(int $companyId): array
    {
        $month = now()->month;
        $year = now()->year;

        return CoaBalance::whereHas('coa', function ($q) {
            $q->whereHas('category', function ($q2) {
                $q2->where('code', 'ASET');
            })->where('code', 'like', '1-1%');
        })->where('year', $year)->where('month', $month)
            ->with('coa')
            ->get()
            ->map(fn ($b) => [
                'account_name' => $b->coa->name ?? 'Unknown',
                'account_number' => $b->coa->code ?? '',
                'balance' => (float) $b->closing_balance,
            ])
            ->toArray();
    }

    protected function loadCashflowChart(int $companyId): void
    {
        $months = collect(range(0, 5))->map(function ($i) {
            $d = now()->subMonths($i);
            return [
                'label' => $d->translatedFormat('M'),
                'start' => $d->copy()->startOfMonth(),
                'end' => $d->copy()->endOfMonth(),
            ];
        })->reverse()->values();

        $this->cashflowChartLabels = $months->pluck('label')->toArray();
        $this->cashflowChartData = $months->map(function ($m) use ($companyId) {
            $inflow = Payment::where('company_id', $companyId)
                ->where('status', 'confirmed')
                ->whereBetween('payment_date', [$m['start'], $m['end']])
                ->sum('amount');

            $outflow = JournalEntry::whereHas('journal', function ($q) use ($companyId, $m) {
                $q->where('company_id', $companyId)
                    ->where('status', 'posted')
                    ->whereBetween('journal_date', [$m['start'], $m['end']]);
            })->whereHas('coa', function ($q) {
                $q->whereHas('category', function ($q2) {
                    $q2->where('code', 'BEBAN');
                });
            })->sum('debit');

            return [
                'inflow' => (float) $inflow,
                'outflow' => (float) $outflow,
            ];
        })->toArray();
    }
}
