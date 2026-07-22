<?php

namespace App\Filament\Pages;

use App\Models\ReportTemplate;
use App\Services\ReportBuilderService;
use App\Services\ReportNlgService;
use Filament\Pages\Page;

class LaporanBisnis extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?int $navigationSort = 1101;

    protected static string $view = 'filament.pages.laporan-bisnis';

    protected static ?string $title = 'Laporan Bisnis';

    public static function getNavigationGroup(): ?string
    {
        return 'Laporan';
    }

    public array $summaryCards = [];
    public array $chartLabels = [];
    public array $chartData = [];
    public array $paymentMethodLabels = [];
    public array $paymentMethodData = [];
    public array $detailTable = [];
    public string $dateFrom;
    public string $dateTo;
    public string $groupBy = 'bulanan';
    public ?ReportTemplate $activeTemplate = null;
    public array $availableTemplates = [];
    public string $nlgSummary = '';
    public string $chartInsight = '';

    public function mount(): void
    {
        $this->dateFrom = request('date_from', now()->startOfYear()->format('Y-m-d'));
        $this->dateTo = request('date_to', now()->format('Y-m-d'));
        $this->groupBy = request('group_by', 'bulanan');

        $this->availableTemplates = ReportTemplate::where('category', 'sales')
            ->where(function ($q) {
                $q->where('is_public', true)
                    ->orWhere('company_id', auth()->user()->company_id);
            })
            ->orderBy('is_system', 'desc')
            ->orderBy('name')
            ->get()
            ->toArray();

        $this->activeTemplate = request('template_id')
            ? ReportTemplate::find(request('template_id'))
            : ReportTemplate::where('slug', 'revenue-summary')->first();

        if ($this->activeTemplate) {
            $this->loadFromTemplate();
        } else {
            $this->loadLegacyData();
        }

        $this->generateNlgSummary();
    }

    protected function loadFromTemplate(): void
    {
        $service = app(ReportBuilderService::class);

        try {
            $params = [
                'date_from' => $this->dateFrom,
                'date_to' => $this->dateTo,
                'group_by' => $this->groupBy,
            ];

            $data = $service->execute($this->activeTemplate, $params);
            $chartData = $service->generateChartData($this->activeTemplate, $params);

            $this->chartLabels = $chartData['labels'] ?? [];
            $this->chartData = ($chartData['datasets'][0]['data'] ?? []);

            $summary = [];
            foreach ($data as $row) {
                $row = (array) $row;
                foreach ($row as $key => $value) {
                    if (is_numeric($value)) {
                        $summary[$key] = ($summary[$key] ?? 0) + (float) $value;
                    }
                }
            }
            $this->summaryCards = $summary;

            $this->detailTable = $data->toArray();
            $this->paymentMethodLabels = [];
            $this->paymentMethodData = [];
        } catch (\Exception $e) {
            $this->loadLegacyData();
        }
    }

    protected function loadLegacyData(): void
    {
        $invoiceRevenue = \App\Models\Invoice::where('status', 'paid')
            ->whereBetween('invoice_date', [$this->dateFrom, $this->dateTo])
            ->sum('total');

        $posRevenue = \App\Models\PosTransaction::where('payment_status', 'paid')
            ->whereBetween('transaction_date', [$this->dateFrom, $this->dateTo])
            ->sum('grand_total');

        $totalRevenue = $invoiceRevenue + $posRevenue;

        $invoiceCount = \App\Models\Invoice::where('status', 'paid')
            ->whereBetween('invoice_date', [$this->dateFrom, $this->dateTo])
            ->count();

        $posCount = \App\Models\PosTransaction::where('payment_status', 'paid')
            ->whereBetween('transaction_date', [$this->dateFrom, $this->dateTo])
            ->count();

        $totalTransaksi = $invoiceCount + $posCount;

        $avgPerTransaksi = $totalTransaksi > 0 ? $totalRevenue / $totalTransaksi : 0;

        $clientCount = \App\Models\Client::count();
        $memberCount = \App\Models\PosMember::count();
        $totalPelanggan = $clientCount + $memberCount;

        $this->summaryCards = [
            'total_revenue' => $totalRevenue,
            'total_transaksi' => $totalTransaksi,
            'avg_per_transaksi' => $avgPerTransaksi,
            'total_pelanggan' => $totalPelanggan,
        ];

        $this->loadRevenueChart();
        $this->loadPaymentMethodChart();
        $this->loadDetailTable();
    }

    protected function loadRevenueChart(): void
    {
        $groupFormat = match ($this->groupBy) {
            'harian' => '%Y-%m-%d',
            'mingguan' => '%Y-%u',
            default => '%Y-%m',
        };

        $invoiceData = \App\Models\Invoice::where('status', 'paid')
            ->whereBetween('invoice_date', [$this->dateFrom, $this->dateTo])
            ->selectRaw("DATE_FORMAT(invoice_date, '{$groupFormat}') as period, SUM(total) as revenue")
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        $posData = \App\Models\PosTransaction::where('payment_status', 'paid')
            ->whereBetween('transaction_date', [$this->dateFrom, $this->dateTo])
            ->selectRaw("DATE_FORMAT(transaction_date, '{$groupFormat}') as period, SUM(grand_total) as revenue")
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        $merged = collect();
        foreach ($invoiceData as $row) {
            $period = $row->period;
            if ($this->groupBy !== 'harian') {
                $period = $this->formatPeriodLabel($row->period);
            }
            $merged->put($period, $merged->get($period, 0) + (float) $row->revenue);
        }
        foreach ($posData as $row) {
            $period = $row->period;
            if ($this->groupBy !== 'harian') {
                $period = $this->formatPeriodLabel($row->period);
            }
            $merged->put($period, $merged->get($period, 0) + (float) $row->revenue);
        }
        $merged = $merged->sortKeys();

        $this->chartLabels = $merged->keys()->toArray();
        $this->chartData = $merged->values()->toArray();
    }

    protected function formatPeriodLabel(string $period): string
    {
        if ($this->groupBy === 'bulanan') {
            $date = \Carbon\Carbon::createFromFormat('Y-m', $period);
            return $date->translatedFormat('M Y');
        }
        if ($this->groupBy === 'mingguan') {
            $parts = explode('-', $period);
            $year = $parts[0];
            $week = (int) ($parts[1] ?? 1);
            return 'Mgg ke-' . $week . ', ' . $year;
        }
        return $period;
    }

    protected function loadPaymentMethodChart(): void
    {
        $paymentMethods = \App\Models\Payment::whereBetween('payment_date', [$this->dateFrom, $this->dateTo])
            ->selectRaw('payment_method_id, SUM(amount) as total')
            ->where('status', 'confirmed')
            ->groupBy('payment_method_id')
            ->with('paymentMethod')
            ->get();

        $posPayments = \App\Models\PosPayment::whereHas('transaction', function ($q) {
            $q->whereBetween('transaction_date', [$this->dateFrom, $this->dateTo])
                ->where('payment_status', 'paid');
        })
            ->selectRaw('payment_method, SUM(amount) as total')
            ->groupBy('payment_method')
            ->get();

        $labels = [];
        $data = [];
        foreach ($paymentMethods as $pm) {
            $name = $pm->paymentMethod?->name ?? 'Unknown';
            $labels[] = $name;
            $data[] = (float) $pm->total;
        }
        foreach ($posPayments as $pp) {
            $labels[] = $pp->payment_method;
            $data[] = (float) $pp->total;
        }

        $this->paymentMethodLabels = $labels;
        $this->paymentMethodData = $data;
    }

    protected function loadDetailTable(): void
    {
        $invoiceData = \App\Models\Invoice::where('status', 'paid')
            ->whereBetween('invoice_date', [$this->dateFrom, $this->dateTo])
            ->selectRaw("DATE_FORMAT(invoice_date, '%Y-%m-%d') as date, 'Invoice' as source_type, invoice_number as reference, total, 'paid' as item_status")
            ->get();

        $posData = \App\Models\PosTransaction::where('payment_status', 'paid')
            ->whereBetween('transaction_date', [$this->dateFrom, $this->dateTo])
            ->selectRaw("DATE_FORMAT(transaction_date, '%Y-%m-%d') as date, 'POS' as source_type, receipt_number as reference, grand_total as total, 'paid' as item_status")
            ->get();

        $combined = $invoiceData->concat($posData)->sortByDesc('date');
        $this->detailTable = $combined->toArray();
    }

    protected function generateNlgSummary(): void
    {
        try {
            $nlg = app(ReportNlgService::class);

            $reportData = [
                'module' => 'Bisnis',
                'periode' => "{$this->dateFrom} s/d {$this->dateTo}",
                'group_by' => $this->groupBy,
                'summary' => $this->summaryCards,
            ];

            $this->nlgSummary = $nlg->generateExecutiveSummary($reportData, 'bisnis');

            if (!empty($this->chartData) && !empty($this->chartLabels)) {
                $chartData = [
                    'labels' => $this->chartLabels,
                    'data' => $this->chartData,
                ];
                $comparison = [
                    'type' => 'revenue',
                    'period' => $this->groupBy,
                ];
                $this->chartInsight = $nlg->chartInsight('line', $chartData, $comparison);
            }
        } catch (\Exception $e) {
            $this->nlgSummary = 'Ringkasan AI tidak tersedia. Menampilkan data numerik.';
            $this->chartInsight = '';
        }
    }
}
