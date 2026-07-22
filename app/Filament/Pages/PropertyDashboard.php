<?php

namespace App\Filament\Pages;

use App\Models\MaintenanceRequest;
use App\Models\PropertyUnit;
use App\Models\ServiceChargeInvoice;
use App\Services\PropertyService;
use Filament\Pages\Page;

class PropertyDashboard extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-pie';

    protected static ?int $navigationSort = 800;

    protected static string $view = 'filament.pages.property-dashboard';

    protected static ?string $title = 'Dashboard Properti';

    public static function getNavigationGroup(): ?string
    {
        return '🏠 Properti';
    }

    public array $occupancyRate = [];
    public float $revenueThisMonth = 0;
    public array $expiringContracts = [];
    public int $pendingMaintenanceCount = 0;
    public int $totalUnits = 0;

    public function mount(): void
    {
        $companyId = auth()->user()->company_id;
        $service = app(PropertyService::class);

        $this->occupancyRate = $service->getOccupancyRate($companyId);
        $this->totalUnits = $this->occupancyRate['total_units'] ?? 0;

        $revenue = $service->getRevenuePerUnit($companyId, 'monthly');
        $this->revenueThisMonth = $revenue['total_revenue'] ?? 0;

        $this->expiringContracts = $service->getExpiringContracts(90)->toArray();

        $this->pendingMaintenanceCount = MaintenanceRequest::where('company_id', $companyId)
            ->whereIn('status', ['reported', 'assigned', 'in_progress'])
            ->count();
    }
}
