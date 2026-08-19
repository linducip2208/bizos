<?php

namespace App\Filament\Pages;

use App\Services\CashFlowForecastService;
use Carbon\Carbon;
use Filament\Pages\Page;

class CashFlowForecast extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-arrow-trending-up';

    protected static ?int $navigationSort = 336;

    protected static ?string $title = 'Cash Flow Forecast';

    protected static ?string $navigationLabel = 'Cash Flow Forecast';

    protected static ?string $slug = 'cash-flow-forecast';

    protected string $view = 'filament.pages.cash-flow-forecast';

    public static function getNavigationGroup(): ?string
    {
        return 'Finance & Accounting';
    }

    public int $days = 90;

    public array $forecast = [];
    public array $sources = [];
    public array $lowBalanceDays = [];
    public array $recommendations = [];
    public float $currentCash = 0;

    public function mount(): void
    {
        $this->load();
    }

    public function setDays(int $days): void
    {
        $allowed = [30, 60, 90, 180];
        $this->days = in_array($days, $allowed, true) ? $days : 90;
        $this->load();
    }

    public function load(): void
    {
        $service = app(CashFlowForecastService::class);

        $this->currentCash = $service->getCurrentCashPosition();
        $this->forecast = $service->generateForecast($this->days);
        $this->sources = $this->forecast['sources'] ?? [];
        $this->lowBalanceDays = $service->getLowBalanceDays($this->forecast);
        $this->recommendations = $service->generateRecommendations($this->forecast);
    }

    public function getProjectedEnding(): float
    {
        return (float) ($this->forecast['projected_ending_balance'] ?? 0);
    }

    public function getNetChange(): float
    {
        return (float) ($this->forecast['net_change'] ?? 0);
    }

    public function getRunwayDays(): int
    {
        $cumulative = $this->forecast['cumulative'] ?? [];
        $threshold = (float) ($this->forecast['threshold'] ?? 0);

        foreach ($cumulative as $i => $balance) {
            if ((float) $balance < $threshold) {
                return $i;
            }
        }

        return count($cumulative);
    }

    public function getChartLabels(): array
    {
        return array_map(
            fn($d) => Carbon::parse($d)->format('d M'),
            $this->forecast['dates'] ?? []
        );
    }

    public function getChartCumulative(): array
    {
        return $this->forecast['cumulative'] ?? [];
    }

    public function getChartInflows(): array
    {
        return $this->forecast['inflows'] ?? [];
    }

    public function getChartOutflows(): array
    {
        return $this->forecast['outflows'] ?? [];
    }
}
