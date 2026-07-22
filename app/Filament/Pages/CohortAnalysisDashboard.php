<?php

namespace App\Filament\Pages;

use App\Services\AdvancedAnalyticsService;
use Filament\Pages\Page;

class CohortAnalysisDashboard extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-table-cells';

    protected static ?int $navigationSort = 1202;

    protected static string $view = 'filament.pages.cohort-analysis';

    protected static ?string $title = 'Analisis Kohort';

    protected static ?string $navigationLabel = 'Kohort';

    protected static ?string $slug = 'cohort-analysis';

    public static function getNavigationGroup(): ?string
    {
        return 'Laporan';
    }

    public array $employeeCohort = [];
    public array $customerCohort = [];

    public function mount(): void
    {
        $this->loadData();
    }

    public function loadData(): void
    {
        $service = app(AdvancedAnalyticsService::class);
        $companyId = auth()->user()->company_id;

        $this->employeeCohort = $service->employeeRetentionCohort($companyId);
        $this->customerCohort = $service->customerRetentionCohort($companyId);
    }

    public function cohortColor($value): string
    {
        if (is_null($value)) return '#f3f4f6';
        if ($value >= 90) return '#065f46';
        if ($value >= 80) return '#047857';
        if ($value >= 70) return '#059669';
        if ($value >= 60) return '#34d399';
        if ($value >= 50) return '#6ee7b7';
        if ($value >= 40) return '#fde68a';
        if ($value >= 30) return '#fbbf24';
        if ($value >= 20) return '#f59e0b';
        return '#ef4444';
    }
}
