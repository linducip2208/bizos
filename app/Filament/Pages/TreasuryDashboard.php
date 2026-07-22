<?php

namespace App\Filament\Pages;

use App\Services\TreasuryService;
use Filament\Pages\Page;

class TreasuryDashboard extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';

    protected static ?int $navigationSort = 1700;

    protected static string $view = 'filament.pages.treasury-dashboard';

    protected static ?string $title = 'Dashboard Treasury';

    protected static ?string $navigationLabel = 'Dashboard Treasury';

    protected static ?string $slug = 'treasury-dashboard';

    public static function getNavigationGroup(): ?string
    {
        return 'Treasury';
    }

    public array $cashPosition = [];
    public array $forexExposure = [];
    public array $investmentPortfolio = [];
    public array $maturitySchedule = [];
    public array $facilitySummary = [];
    public array $dailyCashPosition = [];
    public array $liquidityRatios = [];
    public array $cashPooling = [];
    public array $hedgingSuggestions = [];
    public int $companyId;

    public function mount(): void
    {
        $this->companyId = auth()->user()->company_id;
        $this->loadData();
    }

    public function loadData(): void
    {
        $service = app(TreasuryService::class);

        $this->cashPosition = $service->getCashPosition($this->companyId);
        $this->forexExposure = $service->getForexExposure($this->companyId);
        $this->investmentPortfolio = $service->getInvestmentPortfolio($this->companyId);
        $this->maturitySchedule = $service->getMaturitySchedule($this->companyId);
        $this->dailyCashPosition = $service->getDailyCashPosition($this->companyId, 30);
        $this->liquidityRatios = $service->getLiquidityRatios($this->companyId);
        $this->cashPooling = $service->suggestCashPooling($this->companyId);
        $this->hedgingSuggestions = $service->suggestHedging($this->companyId);

        $facilities = \App\Models\BankFacility::where('company_id', $this->companyId)->active()->get();
        $this->facilitySummary = [];
        foreach ($facilities as $facility) {
            $util = $service->getFacilityUtilization($facility);
            $covenants = $service->checkCovenantCompliance($facility);
            $this->facilitySummary[] = [
                'id' => $facility->id,
                'name' => $facility->name,
                'bank_name' => $facility->bank_name,
                'facility_type' => $facility->facility_type,
                'limit' => $util['limit_formatted'],
                'utilized' => $util['utilized_formatted'],
                'available' => $util['available_formatted'],
                'utilization_percent' => $util['utilization_percent'],
                'expiry_date' => $util['expiry_date'],
                'days_to_expiry' => $util['days_to_expiry'],
                'covenants_compliant' => $covenants['overall_compliant'],
                'breach_count' => $covenants['breach_count'],
            ];
        }
    }
}
