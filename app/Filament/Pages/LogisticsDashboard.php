<?php

namespace App\Filament\Pages;

use App\Models\DeliveryOrder;
use App\Models\FleetGpsTrack;
use App\Models\Vehicle;
use App\Services\LogisticsService;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

class LogisticsDashboard extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-truck';

    protected static ?int $navigationSort = 900;

    protected string $view = 'filament.pages.logistics-dashboard';

    protected static ?string $title = 'Dashboard Logistik';

    public static function getNavigationGroup(): ?string
    {
        return 'Logistik';
    }

    public array $performance = [];
    public array $statusCounts = [];
    public array $gpsTracks = [];
    public array $driverRanking = [];
    public array $mapPoints = [];
    public string $period = 'this_month';
    public int $activeVehicles = 0;

    public function mount(): void
    {
        $this->period = request('period', 'this_month');
        $this->loadData();
    }

    protected function loadData(): void
    {
        $service = app(LogisticsService::class);
        $this->performance = $service->getDeliveryPerformance($this->period);

        $this->statusCounts = DeliveryOrder::query()
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $this->activeVehicles = Vehicle::where('status', 'in_use')->count();

        $this->gpsTracks = FleetGpsTrack::with(['vehicle', 'driver'])
            ->latest('recorded_at')
            ->take(50)
            ->get()
            ->map(fn($track) => [
                'vehicle' => $track->vehicle?->plate_number ?? 'N/A',
                'driver' => $track->driver?->first_name ?? 'N/A',
                'lat' => $track->latitude,
                'lng' => $track->longitude,
                'speed' => $track->speed_kmh,
                'heading' => $track->heading,
                'recorded_at' => $track->recorded_at?->format('d M Y, H:i:s'),
            ])
            ->toArray();

        $latestTracks = FleetGpsTrack::with('vehicle')
            ->whereIn('id', function ($q) {
                $q->selectRaw('MAX(id)')->from('fleet_gps_tracks')->groupBy('vehicle_id');
            })
            ->get();

        $this->mapPoints = $latestTracks->map(fn($track) => [
            'vehicle' => $track->vehicle?->plate_number ?? 'N/A',
            'lat' => $track->latitude,
            'lng' => $track->longitude,
            'speed' => $track->speed_kmh,
            'heading' => $track->heading,
        ])->toArray();

        $driverIds = DeliveryOrder::whereNotNull('driver_id')
            ->distinct('driver_id')
            ->pluck('driver_id');

        $this->driverRanking = [];
        foreach ($driverIds as $driverId) {
            $this->driverRanking[] = $service->getDriverPerformance($driverId, $this->period);
        }

        usort($this->driverRanking, fn($a, $b) => $b['total_deliveries'] <=> $a['total_deliveries']);
    }
}
