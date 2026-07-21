<?php

namespace App\Filament\Pages;

use App\Models\CoaCategory;
use App\Models\JournalEntry;
use Filament\Pages\Page;

class LaporanKeuangan extends Page
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-banknotes';

    protected static ?int $navigationSort = 1102;

    protected string $view = 'filament.pages.laporan-keuangan';

    protected static ?string $title = 'Laporan Keuangan';

    public static function getNavigationGroup(): ?string
    {
        return 'Laporan';
    }

    public array $cards = [];

    public array $pnlSummary = [];

    public array $revenueLabels = [];

    public array $revenueData = [];

    public array $expenseData = [];

    public array $expenseCategoryLabels = [];

    public array $expenseCategoryData = [];

    public string $dateFrom;

    public string $dateTo;

    public function mount(): void
    {
        $this->dateFrom = request('date_from', now()->startOfYear()->format('Y-m-d'));
        $this->dateTo = request('date_to', now()->format('Y-m-d'));

        $this->loadData();
    }

    protected function loadData(): void
    {
        $revenueCategoryIds = CoaCategory::where('normal_balance', 'credit')
            ->pluck('id')
            ->toArray();

        $expenseCategoryIds = CoaCategory::where('normal_balance', 'debit')
            ->pluck('id')
            ->toArray();

        $revenueCoaIds = \App\Models\Coa::whereIn('category_id', $revenueCategoryIds)
            ->where('is_active', true)
            ->pluck('id')
            ->toArray();

        $expenseCoaIds = \App\Models\Coa::whereIn('category_id', $expenseCategoryIds)
            ->where('is_active', true)
            ->pluck('id')
            ->toArray();

        $totalPendapatan = JournalEntry::whereIn('coa_id', $revenueCoaIds)
            ->whereHas('journal', function ($q) {
                $q->whereBetween('journal_date', [$this->dateFrom, $this->dateTo]);
            })
            ->sum('credit');

        $totalBeban = JournalEntry::whereIn('coa_id', $expenseCoaIds)
            ->whereHas('journal', function ($q) {
                $q->whereBetween('journal_date', [$this->dateFrom, $this->dateTo]);
            })
            ->sum('debit');

        $labaRugi = $totalPendapatan - $totalBeban;
        $margin = $totalPendapatan > 0 ? ($labaRugi / $totalPendapatan) * 100 : 0;

        $this->cards = [
            'total_pendapatan' => $totalPendapatan,
            'total_beban' => $totalBeban,
            'laba_rugi' => $labaRugi,
            'margin' => $margin,
        ];

        $this->loadRevenueVsExpenseChart($revenueCoaIds, $expenseCoaIds);
        $this->loadExpenseByCategoryChart();
        $this->loadPnlSummary($revenueCoaIds, $expenseCoaIds);
    }

    protected function loadRevenueVsExpenseChart(array $revenueCoaIds, array $expenseCoaIds): void
    {
        $revenueByMonth = JournalEntry::whereIn('coa_id', $revenueCoaIds)
            ->whereHas('journal', function ($q) {
                $q->whereBetween('journal_date', [$this->dateFrom, $this->dateTo]);
            })
            ->join('journals', 'journal_entries.journal_id', '=', 'journals.id')
            ->selectRaw("DATE_FORMAT(journals.journal_date, '%Y-%m') as period, SUM(journal_entries.credit) as total")
            ->groupBy('period')
            ->orderBy('period')
            ->pluck('total', 'period')
            ->toArray();

        $expenseByMonth = JournalEntry::whereIn('coa_id', $expenseCoaIds)
            ->whereHas('journal', function ($q) {
                $q->whereBetween('journal_date', [$this->dateFrom, $this->dateTo]);
            })
            ->join('journals', 'journal_entries.journal_id', '=', 'journals.id')
            ->selectRaw("DATE_FORMAT(journals.journal_date, '%Y-%m') as period, SUM(journal_entries.debit) as total")
            ->groupBy('period')
            ->orderBy('period')
            ->pluck('total', 'period')
            ->toArray();

        $allPeriods = array_unique(array_merge(array_keys($revenueByMonth), array_keys($expenseByMonth)));
        sort($allPeriods);

        $this->revenueLabels = array_map(function ($p) {
            return \Carbon\Carbon::createFromFormat('Y-m', $p)->translatedFormat('M Y');
        }, $allPeriods);

        $this->revenueData = array_map(fn ($p) => (float) ($revenueByMonth[$p] ?? 0), $allPeriods);
        $this->expenseData = array_map(fn ($p) => (float) ($expenseByMonth[$p] ?? 0), $allPeriods);
    }

    protected function loadExpenseByCategoryChart(): void
    {
        $expenseCategoryIds = CoaCategory::where('normal_balance', 'debit')->pluck('id')->toArray();

        $categories = CoaCategory::whereIn('id', $expenseCategoryIds)->get();

        $labels = [];
        $data = [];

        foreach ($categories as $cat) {
            $coaIds = \App\Models\Coa::where('category_id', $cat->id)
                ->where('is_active', true)
                ->pluck('id')
                ->toArray();

            if (empty($coaIds)) {
                continue;
            }

            $total = JournalEntry::whereIn('coa_id', $coaIds)
                ->whereHas('journal', function ($q) {
                    $q->whereBetween('journal_date', [$this->dateFrom, $this->dateTo]);
                })
                ->sum('debit');

            if ($total > 0) {
                $labels[] = $cat->name;
                $data[] = (float) $total;
            }
        }

        $this->expenseCategoryLabels = $labels;
        $this->expenseCategoryData = $data;
    }

    protected function loadPnlSummary(array $revenueCoaIds, array $expenseCoaIds): void
    {
        $revenueAccounts = \App\Models\Coa::whereIn('id', $revenueCoaIds)->get();

        $expenseAccounts = \App\Models\Coa::whereIn('id', $expenseCoaIds)->get();

        $this->pnlSummary = [];

        $this->pnlSummary[] = [
            'type' => 'header',
            'label' => 'PENDAPATAN',
            'amount' => 0,
        ];

        $totalPendapatan = 0;
        foreach ($revenueAccounts as $coa) {
            $amount = JournalEntry::where('coa_id', $coa->id)
                ->whereHas('journal', function ($q) {
                    $q->whereBetween('journal_date', [$this->dateFrom, $this->dateTo]);
                })
                ->sum('credit');

            $this->pnlSummary[] = [
                'type' => 'line',
                'label' => $coa->name . ' (' . $coa->code . ')',
                'amount' => (float) $amount,
            ];
            $totalPendapatan += (float) $amount;
        }

        $this->pnlSummary[] = [
            'type' => 'subtotal',
            'label' => 'Total Pendapatan',
            'amount' => $totalPendapatan,
        ];

        $this->pnlSummary[] = [
            'type' => 'header',
            'label' => 'BEBAN',
            'amount' => 0,
        ];

        $totalBeban = 0;
        foreach ($expenseAccounts as $coa) {
            $amount = JournalEntry::where('coa_id', $coa->id)
                ->whereHas('journal', function ($q) {
                    $q->whereBetween('journal_date', [$this->dateFrom, $this->dateTo]);
                })
                ->sum('debit');

            $this->pnlSummary[] = [
                'type' => 'line',
                'label' => $coa->name . ' (' . $coa->code . ')',
                'amount' => (float) $amount,
            ];
            $totalBeban += (float) $amount;
        }

        $this->pnlSummary[] = [
            'type' => 'subtotal',
            'label' => 'Total Beban',
            'amount' => $totalBeban,
        ];

        $labaRugi = $totalPendapatan - $totalBeban;

        $this->pnlSummary[] = [
            'type' => 'total',
            'label' => $labaRugi >= 0 ? 'LABA BERSIH' : 'RUGI BERSIH',
            'amount' => $labaRugi,
        ];
    }
}
