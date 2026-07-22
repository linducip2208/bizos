<?php

namespace App\Filament\Pages;

use App\Services\FraudDetectionService;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;

class FraudDetectionDashboard extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-shield-check';

    protected static ?int $navigationSort = 1304;

    protected static ?string $title = 'Deteksi Fraud';

    protected static ?string $navigationLabel = 'Deteksi Fraud';

    protected static ?string $slug = 'fraud-detection';

    protected string $view = 'filament.pages.fraud-detection';

    public static function getNavigationGroup(): ?string
    {
        return 'AI Analytics';
    }

    public ?array $data = [];
    public ?array $scanResult = [];
    public ?array $benfordResult = [];
    public ?string $fraudReport = '';
    public string $period = 'this_month';
    public int $companyId;
    public bool $isScanning = false;
    public string $activeTab = 'scan';

    public function mount(): void
    {
        $this->companyId = auth()->user()->company_id;
        $this->form->fill(['period' => 'this_month']);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('period')
                    ->label('Periode')
                    ->options([
                        'this_month' => 'Bulan Ini',
                        'last_month' => 'Bulan Lalu',
                        'last_30_days' => '30 Hari Terakhir',
                        'last_90_days' => '90 Hari Terakhir',
                        'this_year' => 'Tahun Ini',
                    ])
                    ->default('this_month')
                    ->live()
                    ->afterStateUpdated(fn($state) => $this->period = $state),
            ])
            ->statePath('data');
    }

    public function runScan(): void
    {
        $this->isScanning = true;
        $service = app(FraudDetectionService::class);
        $this->scanResult = $service->scanAll($this->companyId, $this->period);
        $this->benfordResult = $service->benfordAnalysis($this->companyId, $this->period);
        $this->isScanning = false;
        $this->activeTab = 'scan';
    }

    public function generateReport(): void
    {
        $service = app(FraudDetectionService::class);
        $this->fraudReport = $service->generateFraudReport($this->companyId, $this->period);
        $this->activeTab = 'report';
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

    public function getRiskLevelColor(string $level): string
    {
        return match ($level) {
            'high' => '#ef4444',
            'medium' => '#f59e0b',
            'low' => '#3b82f6',
            default => '#6b7280',
        };
    }

    public function getBenfordChartLabels(): array
    {
        if (empty($this->benfordResult)) return [];
        return array_map(fn($d) => "Digit {$d}", array_keys($this->benfordResult['invoice_distribution'] ?? []));
    }

    public function getBenfordActualData(): array
    {
        if (empty($this->benfordResult)) return [];
        return array_column($this->benfordResult['invoice_distribution'] ?? [], 'actual');
    }

    public function getBenfordExpectedData(): array
    {
        if (empty($this->benfordResult)) return [];
        return array_column($this->benfordResult['invoice_distribution'] ?? [], 'expected');
    }
}
