<?php

namespace App\Filament\Pages;

use App\Services\AdvancedAnalyticsService;
use Filament\Pages\Page;

class RfmDashboard extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user-group';

    protected static ?int $navigationSort = 1204;

    protected static string $view = 'filament.pages.rfm-dashboard';

    protected static ?string $title = 'Analisis RFM';

    protected static ?string $navigationLabel = 'RFM';

    protected static ?string $slug = 'rfm-dashboard';

    public static function getNavigationGroup(): ?string
    {
        return 'Laporan';
    }

    public array $rfmData = [];
    public array $segmentSummary = [];

    public function mount(): void
    {
        $this->loadData();
    }

    public function loadData(): void
    {
        $service = app(AdvancedAnalyticsService::class);
        $companyId = auth()->user()->company_id;

        $result = $service->calculateRfm($companyId);
        $this->rfmData = $result['data'] ?? [];
        $this->segmentSummary = $result['segment_summary'] ?? [];
    }

    public function rfmColor(int $score): string
    {
        return match ($score) {
            5 => '#065f46',
            4 => '#059669',
            3 => '#fbbf24',
            2 => '#f59e0b',
            default => '#ef4444',
        };
    }

    public function segmentBadge(string $segment): string
    {
        return match ($segment) {
            'Champions' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900 dark:text-emerald-200',
            'Loyal' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
            'Potential' => 'bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-200',
            'At Risk' => 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200',
            'New' => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200',
            'Lost' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
            default => 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200',
        };
    }
}
