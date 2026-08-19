<?php

namespace App\Filament\Pages;

use App\Models\Branch;
use App\Services\FinancialStatementService;
use Filament\Pages\Page;

class TrialBalance extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-table-cells';

    protected static ?int $navigationSort = 404;

    protected string $view = 'filament.pages.trial-balance';

    protected static ?string $title = 'Neraca Saldo';

    public static function getNavigationGroup(): ?string
    {
        return \App\Filament\Navigation\NavigationGroup::REPORTS->value;
    }

    public string $dateFrom;
    public string $dateTo;
    public ?int $branchId = null;
    public array $branches = [];
    public array $trialBalance = [];

    public function mount(): void
    {
        $companyId = auth()->user()->company_id;
        $this->branches = Branch::where('company_id', $companyId)
            ->orderBy('is_headquarters', 'desc')
            ->orderBy('name')
            ->get()
            ->toArray();

        $this->dateFrom = request('date_from', now()->startOfYear()->format('Y-m-d'));
        $this->dateTo = request('date_to', now()->format('Y-m-d'));
        $this->branchId = request('branch_id') ? (int) request('branch_id') : null;

        $this->loadData();
    }

    protected function loadData(): void
    {
        $service = app(FinancialStatementService::class);

        $this->trialBalance = $service->generateTrialBalance([
            'company_id' => auth()->user()->company_id,
            'branch_id' => $this->branchId,
            'date_from' => $this->dateFrom,
            'date_to' => $this->dateTo,
        ]);
    }

    public function getCompanyName(): string
    {
        return auth()->user()->company?->name ?? config('app.name');
    }

    public function getBranchName(): ?string
    {
        if (!$this->branchId) {
            return null;
        }

        return Branch::find($this->branchId)?->name;
    }

    public function getExportPdfUrl(): string
    {
        return route('laporan.neraca-saldo.pdf', request()->only(['date_from', 'date_to', 'branch_id']));
    }

    public function getExportCsvUrl(): string
    {
        return route('laporan.neraca-saldo.csv', request()->only(['date_from', 'date_to', 'branch_id']));
    }

    public function exportPdf()
    {
        $this->mount();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.trial-balance', [
            'trialBalance' => $this->trialBalance,
            'dateFrom' => $this->dateFrom,
            'dateTo' => $this->dateTo,
            'companyName' => $this->getCompanyName(),
            'branchName' => $this->getBranchName(),
        ]);

        return $pdf->download('neraca-saldo-' . now()->format('Ymd') . '.pdf');
    }

    public function exportCsv()
    {
        $this->mount();

        $filename = 'neraca-saldo-' . now()->format('Ymd') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['Kode COA', 'Nama Akun', 'Saldo Awal Debit', 'Saldo Awal Kredit', 'Debit', 'Kredit', 'Saldo Akhir Debit', 'Saldo Akhir Kredit']);

            foreach ($this->trialBalance['accounts'] ?? [] as $row) {
                fputcsv($handle, [
                    $row['code'] ?? '',
                    $row['name'] ?? '',
                    $row['opening_debit'] ?? 0,
                    $row['opening_credit'] ?? 0,
                    $row['movement_debit'] ?? 0,
                    $row['movement_credit'] ?? 0,
                    $row['closing_debit'] ?? 0,
                    $row['closing_credit'] ?? 0,
                ]);
            }

            $totals = $this->trialBalance['totals'] ?? [];
            fputcsv($handle, [
                'TOTAL',
                '',
                $totals['opening_debit'] ?? 0,
                $totals['opening_credit'] ?? 0,
                $totals['movement_debit'] ?? 0,
                $totals['movement_credit'] ?? 0,
                $totals['closing_debit'] ?? 0,
                $totals['closing_credit'] ?? 0,
            ]);

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
