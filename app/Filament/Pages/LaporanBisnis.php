<?php

namespace App\Filament\Pages;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\PosMember;
use App\Models\PosPayment;
use App\Models\PosTransaction;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class LaporanBisnis extends Page
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?int $navigationSort = 1101;

    protected string $view = 'filament.pages.laporan-bisnis';

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

    public function mount(): void
    {
        $this->dateFrom = request('date_from', now()->startOfYear()->format('Y-m-d'));
        $this->dateTo = request('date_to', now()->format('Y-m-d'));
        $this->groupBy = request('group_by', 'bulanan');

        $this->loadData();
    }

    protected function loadData(): void
    {
        $invoiceRevenue = Invoice::where('status', 'paid')
            ->whereBetween('invoice_date', [$this->dateFrom, $this->dateTo])
            ->sum('total');

        $posRevenue = PosTransaction::where('payment_status', 'paid')
            ->whereBetween('transaction_date', [$this->dateFrom, $this->dateTo])
            ->sum('grand_total');

        $totalRevenue = $invoiceRevenue + $posRevenue;

        $invoiceCount = Invoice::where('status', 'paid')
            ->whereBetween('invoice_date', [$this->dateFrom, $this->dateTo])
            ->count();

        $posCount = PosTransaction::where('payment_status', 'paid')
            ->whereBetween('transaction_date', [$this->dateFrom, $this->dateTo])
            ->count();

        $totalTransaksi = $invoiceCount + $posCount;

        $avgPerTransaksi = $totalTransaksi > 0 ? $totalRevenue / $totalTransaksi : 0;

        $clientCount = Client::count();
        $memberCount = PosMember::count();
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

        $labelFormat = match ($this->groupBy) {
            'harian' => 'Y-m-d',
            'mingguan' => '\\M\\i\\n\\g\\g\\u \\k\\e-W, Y',
            default => 'M Y',
        };

        $invoiceData = Invoice::where('status', 'paid')
            ->whereBetween('invoice_date', [$this->dateFrom, $this->dateTo])
            ->selectRaw("DATE_FORMAT(invoice_date, '{$groupFormat}') as period, SUM(total) as revenue")
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        $posData = PosTransaction::where('payment_status', 'paid')
            ->whereBetween('transaction_date', [$this->dateFrom, $this->dateTo])
            ->selectRaw("DATE_FORMAT(transaction_date, '{$groupFormat}') as period, SUM(grand_total) as revenue")
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        $merged = collect();

        foreach ($invoiceData as $row) {
            $period = $row->period;
            if ($this->groupBy !== 'harian') {
                $period = $this->formatPeriodLabel($row->period, $labelFormat);
            }
            $merged->put($period, $merged->get($period, 0) + (float) $row->revenue);
        }

        foreach ($posData as $row) {
            $period = $row->period;
            if ($this->groupBy !== 'harian') {
                $period = $this->formatPeriodLabel($row->period, $labelFormat);
            }
            $merged->put($period, $merged->get($period, 0) + (float) $row->revenue);
        }

        $merged = $merged->sortKeys();

        $this->chartLabels = $merged->keys()->toArray();
        $this->chartData = $merged->values()->toArray();
    }

    protected function formatPeriodLabel(string $period, string $format): string
    {
        if ($this->groupBy === 'bulanan') {
            $date = \Carbon\Carbon::createFromFormat('Y-m', $period);
            return $date->translatedFormat('M Y');
        }

        if ($this->groupBy === 'mingguan') {
            $parts = explode('-', $period);
            $year = $parts[0];
            $week = (int) ($parts[1] ?? 1);
            $date = \Carbon\Carbon::now()->setISODate((int) $year, $week);

            return 'Mgg ke-' . $week . ', ' . $year;
        }

        return $period;
    }

    protected function loadPaymentMethodChart(): void
    {
        $paymentMethods = Payment::whereBetween('payment_date', [$this->dateFrom, $this->dateTo])
            ->selectRaw('payment_method_id, SUM(amount) as total')
            ->where('status', 'confirmed')
            ->groupBy('payment_method_id')
            ->with('paymentMethod')
            ->get();

        $posPayments = PosPayment::whereHas('transaction', function ($q) {
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
        $invoiceData = Invoice::where('status', 'paid')
            ->whereBetween('invoice_date', [$this->dateFrom, $this->dateTo])
            ->selectRaw("
                DATE_FORMAT(invoice_date, '%Y-%m-%d') as date,
                'Invoice' as source_type,
                invoice_number as reference,
                total,
                'paid' as item_status
            ")
            ->get();

        $posData = PosTransaction::where('payment_status', 'paid')
            ->whereBetween('transaction_date', [$this->dateFrom, $this->dateTo])
            ->selectRaw("
                DATE_FORMAT(transaction_date, '%Y-%m-%d') as date,
                'POS' as source_type,
                receipt_number as reference,
                grand_total as total,
                'paid' as item_status
            ")
            ->get();

        $combined = $invoiceData->concat($posData)->sortByDesc('date');

        $this->detailTable = $combined->toArray();
    }
}
