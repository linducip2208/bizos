<?php

namespace App\Filament\Pages;

use App\Services\MrpService;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class MrpDashboard extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calculator';

    protected static ?int $navigationSort = 9;

    protected static ?string $title = 'MRP Dashboard';

    protected static ?string $navigationLabel = 'MRP Dashboard';

    protected static ?string $slug = 'mrp-dashboard';

    protected static string $view = 'filament.pages.mrp-dashboard';

    public static function getNavigationGroup(): ?string
    {
        return 'Manufaktur';
    }

    public array $exceptions = [];
    public array $purchaseSuggestions = [];
    public array $productionSuggestions = [];
    public int $horizonDays = 30;
    public ?int $companyId = null;
    public bool $hasRun = false;

    public function mount(): void
    {
        $user = auth()->user();
        $this->companyId = $user?->employee?->company_id;
        $this->runMrp();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('run_mrp')
                ->label('Jalankan MRP')
                ->icon('heroicon-o-play')
                ->color('indigo')
                ->form([
                    \Filament\Forms\Components\Select::make('company_id')
                        ->label('Perusahaan')
                        ->options(\App\Models\Company::pluck('name', 'id'))
                        ->searchable()
                        ->default($this->companyId),
                    TextInput::make('horizon_days')
                        ->label('Horizon (hari)')
                        ->integer()
                        ->default(30)
                        ->minValue(7)
                        ->maxValue(365),
                ])
                ->action(function (array $data) {
                    $this->companyId = (int) $data['company_id'];
                    $this->horizonDays = (int) $data['horizon_days'];
                    $this->runMrp();
                }),
            Action::make('refresh')
                ->label('Refresh')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->action(fn() => $this->runMrp()),
        ];
    }

    public function runMrp(): void
    {
        if (!$this->companyId) {
            return;
        }

        $mrpService = app(MrpService::class);

        $this->exceptions = $mrpService->getExceptions($this->companyId, $this->horizonDays);
        $this->purchaseSuggestions = $mrpService->generatePurchaseSuggestions($this->companyId, $this->horizonDays);
        $this->productionSuggestions = $mrpService->generateProductionSuggestions($this->companyId, $this->horizonDays);
        $this->hasRun = true;
    }

    public function getViewData(): array
    {
        return [
            'exceptions' => $this->exceptions,
            'purchaseSuggestions' => $this->purchaseSuggestions,
            'productionSuggestions' => $this->productionSuggestions,
            'horizonDays' => $this->horizonDays,
            'hasRun' => $this->hasRun,
        ];
    }
}
