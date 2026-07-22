<?php

namespace App\Filament\Pages;

use App\Models\Product;
use App\Services\SalesForecastService;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;

class SalesForecastDashboard extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?int $navigationSort = 1301;

    protected static ?string $title = 'Forecast Penjualan';

    protected static ?string $navigationLabel = 'Forecast Penjualan';

    protected static ?string $slug = 'sales-forecast';

    protected static string $view = 'filament.pages.sales-forecast';

    public static function getNavigationGroup(): ?string
    {
        return 'AI Analytics';
    }

    public ?array $data = [];
    public ?array $forecast = [];
    public ?array $accuracy = [];
    public ?array $revenueForecast = [];
    public ?string $narrative = '';
    public int $horizonDays = 30;
    public int $companyId;

    public function mount(): void
    {
        $this->companyId = auth()->user()->company_id;
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('product_id')
                    ->label('Pilih Produk')
                    ->options(
                        Product::where('company_id', $this->companyId)
                            ->where('is_active', true)
                            ->pluck('name', 'id')
                    )
                    ->searchable()
                    ->nullable()
                    ->placeholder('Semua Produk (Total Revenue)'),
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
                    ->afterStateUpdated(fn($state) => $this->horizonDays = (int) $state),
            ])
            ->statePath('data');
    }

    public function loadForecast(): void
    {
        $service = app(SalesForecastService::class);

        $productId = $this->data['product_id'] ?? null;

        if ($productId) {
            $this->forecast = $service->forecastPerProduct((int) $productId, $this->horizonDays);
            $this->revenueForecast = [];
        } else {
            $this->revenueForecast = $service->forecastRevenue($this->companyId, $this->horizonDays);
            $this->forecast = $this->revenueForecast;
        }

        $this->accuracy = $service->getForecastAccuracy($this->companyId);
        $this->narrative = $service->generateNarrative($this->forecast ?: $this->revenueForecast ?: []);
    }

    public function getChartLabels(): array
    {
        return array_column($this->forecast ?: [], 'date');
    }

    public function getChartPredicted(): array
    {
        return array_column($this->forecast ?: [], 'predicted_quantity');
    }

    public function getChartLow(): array
    {
        return array_column($this->forecast ?: [], 'confidence_low');
    }

    public function getChartHigh(): array
    {
        return array_column($this->forecast ?: [], 'confidence_high');
    }

    public function getTrendSummary(): array
    {
        if (empty($this->forecast)) return ['direction' => 'stable', 'pct' => 0, 'label' => 'Tidak ada data'];

        $first = $this->forecast[0]['predicted_quantity'] ?? 0;
        $last = $this->forecast[count($this->forecast) - 1]['predicted_quantity'] ?? 0;
        $total = array_sum(array_column($this->forecast, 'predicted_quantity'));
        $avg = $total / max(1, count($this->forecast));
        $upDays = count(array_filter($this->forecast, fn($f) => ($f['trend_direction'] ?? '') === 'up'));
        $downDays = count(array_filter($this->forecast, fn($f) => ($f['trend_direction'] ?? '') === 'down'));

        $pct = $first > 0 ? round((($last - $first) / $first) * 100, 1) : 0;
        $direction = $pct > 2 ? 'up' : ($pct < -2 ? 'down' : 'stable');

        return [
            'direction' => $direction,
            'pct' => $pct,
            'label' => $direction === 'up' ? "Naik {$pct}%" : ($direction === 'down' ? "Turun " . abs($pct) . '%' : 'Stabil'),
            'total_proyeksi' => round($total, 2),
            'rata_rata_harian' => round($avg, 2),
            'hari_naik' => $upDays,
            'hari_turun' => $downDays,
        ];
    }
}
