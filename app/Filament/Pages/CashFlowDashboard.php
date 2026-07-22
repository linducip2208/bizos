<?php

namespace App\Filament\Pages;

use App\Services\CashFlowForecastService;
use App\Services\TreasuryService;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;

class CashFlowDashboard extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';

    protected static ?int $navigationSort = 1302;

    protected static ?string $title = 'Forecast Cash Flow';

    protected static ?string $navigationLabel = 'Forecast Cash Flow';

    protected static ?string $slug = 'cash-flow';

    protected string $view = 'filament.pages.cash-flow';

    public static function getNavigationGroup(): ?string
    {
        return 'AI Analytics';
    }

    public ?array $data = [];
    public ?array $forecast = [];
    public ?array $alerts = [];
    public ?array $scenario = [];
    public ?array $liquidityRatios = [];
    public ?array $treasuryDailyPositions = [];
    public int $horizonDays = 30;
    public int $companyId;
    public string $mode = 'baseline';

    public function mount(): void
    {
        $this->companyId = auth()->user()->company_id;
        $this->form->fill([
            'horizon_days' => 30,
            'delay_receivables' => 0,
            'additional_expense' => 0,
            'expense_date' => now()->addDays(7)->format('Y-m-d'),
            'additional_income' => 0,
            'income_date' => now()->addDays(14)->format('Y-m-d'),
        ]);
        $this->loadForecast();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('horizon_days')
                    ->label('Jangka Waktu')
                    ->options([
                        7 => '7 Hari',
                        14 => '14 Hari',
                        30 => '30 Hari',
                        60 => '60 Hari',
                        90 => '90 Hari',
                    ])
                    ->default(30)
                    ->live()
                    ->afterStateUpdated(function ($state) {
                        $this->horizonDays = (int) $state;
                        $this->loadForecast();
                    }),
            ])
            ->statePath('data');
    }

    public function loadForecast(): void
    {
        $service = app(CashFlowForecastService::class);
        $this->forecast = $service->forecastCashPosition($this->companyId, $this->horizonDays);
        $this->alerts = $service->getCashShortageAlerts($this->companyId);
        $this->scenario = [];
        $this->mode = 'baseline';

        $treasury = app(TreasuryService::class);
        $this->liquidityRatios = $treasury->getLiquidityRatios($this->companyId);
        $this->treasuryDailyPositions = $treasury->getDailyCashPosition($this->companyId, $this->horizonDays);
    }

    public function runScenario(): void
    {
        $service = app(CashFlowForecastService::class);
        $changes = [
            'delay_receivables_days' => (int) ($this->data['delay_receivables'] ?? 0),
            'additional_expense' => (int) ($this->data['additional_expense'] ?? 0),
            'expense_date' => $this->data['expense_date'] ?? now()->addDays(7)->format('Y-m-d'),
            'additional_income' => (int) ($this->data['additional_income'] ?? 0),
            'income_date' => $this->data['income_date'] ?? now()->addDays(14)->format('Y-m-d'),
        ];
        $this->scenario = $service->simulateScenario($this->companyId, $changes);
        $this->mode = 'scenario';
    }

    public function getChartLabels(): array
    {
        $data = $this->mode === 'scenario' && !empty($this->scenario) ? $this->scenario : $this->forecast;
        return array_column($data, 'date');
    }

    public function getChartBalance(): array
    {
        $data = $this->mode === 'scenario' && !empty($this->scenario) ? $this->scenario : $this->forecast;
        return array_column($data, 'closing_balance');
    }

    public function getChartInflow(): array
    {
        $data = $this->mode === 'scenario' && !empty($this->scenario) ? $this->scenario : $this->forecast;
        return array_column($data, 'projected_inflow');
    }

    public function getChartOutflow(): array
    {
        $data = $this->mode === 'scenario' && !empty($this->scenario) ? $this->scenario : $this->forecast;
        return array_column($data, 'projected_outflow');
    }

    public function getStats(): array
    {
        if (empty($this->forecast)) return ['current' => 0, 'end' => 0, 'min' => 0, 'max' => 0, 'critical_days' => 0, 'warning_days' => 0];

        $balances = array_column($this->forecast, 'closing_balance');
        return [
            'current' => round($this->forecast[0]['closing_balance'] - $this->forecast[0]['net_flow'], 0),
            'end' => round(end($balances), 0),
            'min' => round(min($balances), 0),
            'max' => round(max($balances), 0),
            'critical_days' => count(array_filter($this->forecast, fn($f) => $f['alert_level'] === 'critical')),
            'warning_days' => count(array_filter($this->forecast, fn($f) => $f['alert_level'] === 'warning')),
        ];
    }
}
