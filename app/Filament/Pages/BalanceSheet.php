<?php

namespace App\Filament\Pages;

use App\Models\Branch;
use App\Services\FinancialStatementService;
use Filament\Pages\Page;

class BalanceSheet extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-scale';

    protected static ?int $navigationSort = 403;

    protected string $view = 'filament.pages.balance-sheet';

    protected static ?string $title = 'Neraca';

    public static function getNavigationGroup(): ?string
    {
        return '📊 Reports & Analytics';
    }

    public string $asOfDate;
    public ?int $branchId = null;
    public array $branches = [];
    public array $balanceSheet = [];

    public function mount(): void
    {
        $companyId = auth()->user()->company_id;
        $this->branches = Branch::where('company_id', $companyId)
            ->orderBy('is_headquarters', 'desc')
            ->orderBy('name')
            ->get()
            ->toArray();

        $this->asOfDate = request('as_of_date', now()->format('Y-m-d'));
        $this->branchId = request('branch_id') ? (int) request('branch_id') : null;

        $this->loadData();
    }

    protected function loadData(): void
    {
        $service = app(FinancialStatementService::class);

        $this->balanceSheet = $service->generateBalanceSheet([
            'company_id' => auth()->user()->company_id,
            'branch_id' => $this->branchId,
            'as_of_date' => $this->asOfDate,
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
        return route('laporan.neraca.pdf', request()->only(['as_of_date', 'branch_id']));
    }

    public function exportPdf()
    {
        $this->mount();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.balance-sheet', [
            'balanceSheet' => $this->balanceSheet,
            'asOfDate' => $this->asOfDate,
            'companyName' => $this->getCompanyName(),
            'branchName' => $this->getBranchName(),
        ]);

        return $pdf->download('neraca-' . now()->format('Ymd') . '.pdf');
    }
}
