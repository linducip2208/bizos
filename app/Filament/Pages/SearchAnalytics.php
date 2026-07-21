<?php

namespace App\Filament\Pages;

use App\Services\EnterpriseSearchService;
use Filament\Pages\Page;

class SearchAnalytics extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-magnifying-glass-circle';

    protected static ?int $navigationSort = 999;

    protected string $view = 'filament.pages.search-analytics';

    protected static ?string $title = 'Analitik Pencarian';

    protected static ?string $navigationLabel = 'Search Analytics';

    protected static ?string $slug = 'search-analytics';

    public array $analytics = [];
    public string $period = '7d';
    public array $topQueries = [];
    public array $zeroResultQueries = [];
    public array $dailyTrend = [];
    public int $totalSearches = 0;
    public int $uniqueSearchers = 0;
    public float $avgResults = 0;
    public float $avgTime = 0;

    public function mount(): void
    {
        $this->loadAnalytics();
    }

    public function loadAnalytics(): void
    {
        $service = app(EnterpriseSearchService::class);
        $this->analytics = $service->getAnalytics($this->period);
        $this->topQueries = $service->getTopQueries($this->period, 20);
        $this->zeroResultQueries = $service->getZeroResultQueries($this->period, 20);
        $this->dailyTrend = $service->getDailyTrend($this->period);
        $this->totalSearches = $service->getTotalSearches($this->period);
        $this->uniqueSearchers = $service->getUniqueSearchers($this->period);
        $this->avgResults = $service->getAvgResults($this->period);
        $this->avgTime = $service->getAvgTime($this->period);
    }
}