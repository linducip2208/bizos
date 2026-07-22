<?php

namespace App\Filament\Pages;

use App\Models\IotDevice;
use App\Models\IotAlert;
use App\Models\EnergyMeter;
use App\Services\IotService;
use Filament\Pages\Page;

class IotDashboard extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cpu-chip';

    protected static ?int $navigationSort = 1600;

    protected string $view = 'filament.pages.iot-dashboard';

    protected static ?string $title = 'Dashboard IoT';

    protected static ?string $navigationLabel = 'Dashboard IoT';

    protected static ?string $slug = 'iot-dashboard';

    public static function getNavigationGroup(): ?string
    {
        return 'IoT & Sensor';
    }

    public array $devices = [];
    public array $deviceStats = [];
    public array $activeAlerts = [];
    public array $energyConsumption = [];
    public array $offlineDevices = [];
    public int $totalDevices = 0;
    public int $onlineDevices = 0;
    public int $offlineDevicesCount = 0;
    public int $activeAlertsCount = 0;
    public int $criticalAlertsCount = 0;
    public int $companyId;

    public function mount(): void
    {
        $this->companyId = auth()->user()->company_id;
        $this->loadData();
    }

    public function loadData(): void
    {
        $iotService = app(IotService::class);

        $this->devices = IotDevice::where('company_id', $this->companyId)
            ->with(['readings' => fn($q) => $q->recent()->limit(1)])
            ->get()
            ->map(function ($device) use ($iotService) {
                $prediction = $iotService->predictFailure($device);
                $latestReading = $device->readings->first();

                return [
                    'id' => $device->id,
                    'name' => $device->name,
                    'type' => $device->type,
                    'status' => $device->status,
                    'location' => $device->location,
                    'battery_level' => $device->battery_level,
                    'last_seen_at' => $device->last_seen_at?->diffForHumans(),
                    'temperature' => $latestReading?->temperature_celsius,
                    'humidity' => $latestReading?->humidity_percent,
                    'vibration' => $latestReading?->vibration_mm_s,
                    'risk_level' => $prediction['risk_level'],
                    'failure_probability' => $prediction['failure_probability_percent'],
                ];
            })->toArray();

        $this->totalDevices = IotDevice::where('company_id', $this->companyId)->active()->count();
        $this->onlineDevices = IotDevice::where('company_id', $this->companyId)->online()->active()->count();
        $this->offlineDevicesCount = IotDevice::where('company_id', $this->companyId)->active()->where('status', 'offline')->count();

        $this->activeAlerts = IotAlert::where('company_id', $this->companyId)
            ->active()
            ->with('device')
            ->orderByDesc('created_at')
            ->take(20)
            ->get()
            ->toArray();

        $this->activeAlertsCount = IotAlert::where('company_id', $this->companyId)->active()->count();
        $this->criticalAlertsCount = IotAlert::where('company_id', $this->companyId)->active()->critical()->count();

        $this->energyConsumption = $iotService->getEnergyConsumption($this->companyId, 'monthly');

        $this->offlineDevices = IotDevice::where('company_id', $this->companyId)
            ->active()
            ->where('status', 'offline')
            ->get()
            ->toArray();
    }
}
