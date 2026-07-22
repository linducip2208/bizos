<?php

namespace App\Filament\Pages;

use App\Services\AdvancedAnalyticsService;
use Filament\Pages\Page;

class FunnelAnalysisDashboard extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-funnel';

    protected static ?int $navigationSort = 1203;

    protected static string $view = 'filament.pages.funnel-analysis';

    protected static ?string $title = 'Analisis Funnel';

    protected static ?string $navigationLabel = 'Funnel';

    protected static ?string $slug = 'funnel-analysis';

    public static function getNavigationGroup(): ?string
    {
        return 'Laporan';
    }

    public array $recruitmentFunnel = [];
    public array $salesFunnel = [];
    public array $purchaseFunnel = [];
    public string $period = 'month';

    public function mount(): void
    {
        $this->period = request('period', 'month');
        $this->loadData();
    }

    public function loadData(): void
    {
        $service = app(AdvancedAnalyticsService::class);

        $this->recruitmentFunnel = $service->recruitmentFunnel($this->period);
        $this->salesFunnel = $service->salesFunnel($this->period);
        $this->purchaseFunnel = $service->purchaseFunnel($this->period);
    }

    public function setPeriod(string $period): void
    {
        $this->period = $period;
        $this->loadData();
    }
}
