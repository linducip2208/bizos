<?php

namespace App\Filament\Pages;

use App\Models\CarbonCalculation;
use App\Models\Company;
use App\Models\Employee;
use App\Models\EsgTarget;
use App\Services\EsgService;
use Filament\Pages\Page;

class EsgDashboard extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-globe-alt';

    protected static ?int $navigationSort = 0;

    protected static string $view = 'filament.pages.esg-dashboard';

    protected static ?string $title = 'Dashboard ESG';

    public array $carbonData = [];
    public array $esgScore = [];
    public array $socialMetrics = [];
    public array $governanceMetrics = [];
    public array $targetProgress = [];
    public array $wasteStats = [];
    public array $waterStats = [];
    public array $reductionSuggestions = [];

    public static function getNavigationGroup(): ?string
    {
        return 'ESG';
    }

    public function mount(): void
    {
        $user = auth()->user();
        if (!$user || !$user->company_id) {
            return;
        }

        $companyId = $user->company_id;
        $period = now()->format('Y-m');

        $esgService = app(EsgService::class);

        $this->carbonData = $esgService->getTotalCarbonFootprint($companyId, $period);
        $this->esgScore = $esgService->getEsgScore($companyId);
        $this->socialMetrics = $esgService->getSocialMetrics($companyId);
        $this->governanceMetrics = $esgService->getGovernanceMetrics($companyId);
        $this->targetProgress = $esgService->getTargetProgress($companyId);
        $this->wasteStats = $esgService->getWasteStats($companyId, $period);
        $this->waterStats = $esgService->getWaterStats($companyId, $period);
        $this->reductionSuggestions = $esgService->suggestReduction($companyId);
    }
}
