<?php

namespace App\Filament\Pages;

use App\Models\Department;
use App\Models\Employee;
use App\Services\FlightRiskService;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;

class FlightRiskDashboard extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user-group';

    protected static ?int $navigationSort = 1303;

    protected static ?string $title = 'Risiko Turnover';

    protected static ?string $navigationLabel = 'Risiko Turnover';

    protected static ?string $slug = 'flight-risk';

    protected string $view = 'filament.pages.flight-risk';

    public static function getNavigationGroup(): ?string
    {
        return 'AI Analytics';
    }

    public ?array $data = [];
    public ?array $topRisks = [];
    public ?array $departmentSummary = [];
    public ?array $retentionPlan = [];
    public ?int $selectedEmployeeId = null;
    public int $companyId;

    public function mount(): void
    {
        $this->companyId = auth()->user()->company_id;
        $this->form->fill();
        $this->loadTopRisks();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('department_id')
                    ->label('Filter Departemen')
                    ->options(
                        Department::where('company_id', $this->companyId)
                            ->pluck('name', 'id')
                    )
                    ->searchable()
                    ->nullable()
                    ->placeholder('Semua Departemen')
                    ->live()
                    ->afterStateUpdated(function ($state) {
                        if ($state) {
                            $this->loadDepartmentSummary((int) $state);
                        } else {
                            $this->loadTopRisks();
                        }
                    }),
            ])
            ->statePath('data');
    }

    public function loadTopRisks(): void
    {
        $service = app(FlightRiskService::class);
        $this->topRisks = $service->getTopRisks($this->companyId, 20);
        $this->departmentSummary = [];
    }

    public function loadDepartmentSummary(int $departmentId): void
    {
        $service = app(FlightRiskService::class);
        $this->departmentSummary = $service->getDepartmentRiskSummary($departmentId);
        $this->topRisks = $this->departmentSummary['employees'] ?? [];
    }

    public function viewRetentionPlan(int $employeeId): void
    {
        $this->selectedEmployeeId = $employeeId;
        $employee = Employee::find($employeeId);
        if ($employee) {
            $service = app(FlightRiskService::class);
            $this->retentionPlan = $service->generateRetentionPlan($employee);
        }
    }

    public function getRiskLevelColor(string $level): string
    {
        return match ($level) {
            'critical' => '#ef4444',
            'high' => '#f97316',
            'medium' => '#f59e0b',
            'low' => '#22c55e',
            default => '#6b7280',
        };
    }

    public function getRiskLevelBg(string $level): string
    {
        return match ($level) {
            'critical' => 'bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-400',
            'high' => 'bg-orange-50 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400',
            'medium' => 'bg-yellow-50 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
            'low' => 'bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-400',
            default => 'bg-gray-50 text-gray-700 dark:bg-gray-900/30 dark:text-gray-400',
        };
    }

    public function getSummaryStats(): array
    {
        if (empty($this->topRisks)) return ['critical' => 0, 'high' => 0, 'medium' => 0, 'low' => 0, 'avg' => 0, 'total' => 0];

        $critical = count(array_filter($this->topRisks, fn($r) => $r['risk_level'] === 'critical'));
        $high = count(array_filter($this->topRisks, fn($r) => $r['risk_level'] === 'high'));
        $medium = count(array_filter($this->topRisks, fn($r) => $r['risk_level'] === 'medium'));
        $low = count(array_filter($this->topRisks, fn($r) => $r['risk_level'] === 'low'));
        $total = count($this->topRisks);
        $avg = $total > 0 ? round(array_sum(array_column($this->topRisks, 'risk_score')) / $total, 1) : 0;

        return compact('critical', 'high', 'medium', 'low', 'avg', 'total');
    }
}
