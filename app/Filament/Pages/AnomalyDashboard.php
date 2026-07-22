<?php

namespace App\Filament\Pages;

use App\Services\AnomalyDetectionService;
use Filament\Pages\Page;

class AnomalyDashboard extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-shield-exclamation';

    protected static ?int $navigationSort = 1201;

    protected static string $view = 'filament.pages.anomaly-dashboard';

    protected static ?string $title = 'Dashboard Anomali';

    protected static ?string $navigationLabel = 'Anomali';

    protected static ?string $slug = 'anomaly-dashboard';

    public static function getNavigationGroup(): ?string
    {
        return 'Laporan';
    }

    public array $anomalies = [];
    public array $stats = [];
    public array $anomaliesByModule = [];
    public string $activeFilter = 'all';
    public int $companyId;

    public function mount(): void
    {
        $this->companyId = auth()->user()->company_id;
        $this->activeFilter = request('filter', 'all');
        $this->loadAnomalies();
    }

    public function loadAnomalies(): void
    {
        $service = app(AnomalyDetectionService::class);
        $this->anomalies = $service->scanAll($this->companyId);

        $this->stats = [
            'total' => count($this->anomalies),
            'high' => count(array_filter($this->anomalies, fn($a) => $a['severity'] === 'high')),
            'medium' => count(array_filter($this->anomalies, fn($a) => $a['severity'] === 'medium')),
            'low' => count(array_filter($this->anomalies, fn($a) => $a['severity'] === 'low')),
        ];

        $this->anomaliesByModule = [];
        foreach ($this->anomalies as $anomaly) {
            $module = $anomaly['module'] ?? 'Lainnya';
            $this->anomaliesByModule[$module][] = $anomaly;
        }
    }

    public function getFilteredAnomalies(): array
    {
        if ($this->activeFilter === 'all') {
            return $this->anomalies;
        }

        $severity = $this->activeFilter;
        $normalized = match ($severity) {
            'high' => 'high',
            'medium' => 'medium',
            'low' => 'low',
            default => null,
        };

        if (!$normalized) {
            $moduleName = match ($severity) {
                'Payroll' => 'Payroll',
                'Finance' => 'Finance',
                'Inventory' => 'Inventory',
                'Attendance' => 'Attendance',
                default => null,
            };
            if ($moduleName) {
                return array_filter($this->anomalies, fn($a) => ($a['module'] ?? '') === $moduleName);
            }
            return $this->anomalies;
        }

        return array_filter($this->anomalies, fn($a) => $a['severity'] === $normalized);
    }

    public function getSeverityColor(string $severity): string
    {
        return match ($severity) {
            'high' => 'danger',
            'medium' => 'warning',
            'low' => 'info',
            default => 'gray',
        };
    }

    public function getSeverityIcon(string $severity): string
    {
        return match ($severity) {
            'high' => 'heroicon-o-exclamation-circle',
            'medium' => 'heroicon-o-exclamation-triangle',
            'low' => 'heroicon-o-information-circle',
            default => 'heroicon-o-question-mark-circle',
        };
    }

    public function refreshAnomalies(): void
    {
        $this->loadAnomalies();
    }
}
