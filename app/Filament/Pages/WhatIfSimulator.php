<?php

namespace App\Filament\Pages;

use App\Models\Product;
use App\Models\WorkCenter;
use App\Services\AdvancedAnalyticsService;
use Filament\Pages\Page;

class WhatIfSimulator extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calculator';

    protected static ?int $navigationSort = 1205;

    protected static string $view = 'filament.pages.what-if-simulator';

    protected static ?string $title = 'Simulasi What-If';

    protected static ?string $navigationLabel = 'What-If Simulator';

    protected static ?string $slug = 'what-if-simulator';

    public static function getNavigationGroup(): ?string
    {
        return 'Laporan';
    }

    public string $simType = 'salary';
    public ?array $result = null;
    public float $increasePercent = 10;
    public ?int $workCenterId = null;
    public float $additionalCapacity = 10;
    public ?int $productId = null;
    public float $newPrice = 0;
    public bool $hasSimulated = false;

    public function mount(): void
    {
        $this->simType = request('type', 'salary');
        $this->workCenterId = WorkCenter::first()?->id;
        $this->productId = Product::first()?->id;
        if ($this->productId && $this->newPrice === 0.0) {
            $product = Product::find($this->productId);
            $this->newPrice = $product ? ($product->selling_price ?? $product->price ?? 0) : 0;
        }
    }

    public function simulate(): void
    {
        $service = app(AdvancedAnalyticsService::class);
        $companyId = auth()->user()->company_id;

        $this->result = match ($this->simType) {
            'salary' => $service->simulateSalaryIncrease($this->increasePercent, $companyId),
            'machine' => $service->simulateNewMachine($this->workCenterId, $this->additionalCapacity),
            'price' => $service->simulatePriceChange($this->productId, $this->newPrice),
            default => null,
        };

        $this->hasSimulated = true;
    }
}
