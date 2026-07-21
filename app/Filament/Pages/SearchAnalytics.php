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
    public float $ctr = 0;

    public function mount(): void
    {
        $this->loadAnalytics();
    }

    public function loadAnalytics(): void
    {
        $service = app(EnterpriseSearchService::class);
        $analytics = $service->getSearchAnalytics($this->period);
        $this->analytics = $analytics;
        $this->topQueries = $service->getPopularSearches(20);
        $this->zeroResultQueries = $service->getZeroResultSearches(20);
        $this->dailyTrend = $analytics['daily_trend'] ?? [];
        $this->totalSearches = $analytics['total_searches'] ?? 0;
        $this->uniqueSearchers = $analytics['unique_searchers'] ?? 0;
        $this->avgResults = $analytics['avg_results_per_search'] ?? 0;
        $this->avgTime = $analytics['avg_search_time_ms'] ?? 0;
        $this->ctr = (float) ($analytics['click_through_rate_pct'] ?? 0);
    }

    public function setPeriod(string $period): void
    {
        $this->period = $period;
        $this->loadAnalytics();
    }
}