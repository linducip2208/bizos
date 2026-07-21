<?php

namespace App\Console\Commands;

use App\Models\Vehicle;
use App\Services\LogisticsService;
use Illuminate\Console\Command;

class RecordFleetGps extends Command
{
    protected $signature = 'logistics:record-gps';
    protected $description = 'Merekam data GPS armada kendaraan setiap 2 menit';

    public function handle(): void
    {
        $service = app(LogisticsService::class);
        $vehicles = Vehicle::where('status', 'in_use')->get();

        if ($vehicles->isEmpty()) {
            $this->info('Tidak ada kendaraan yang sedang digunakan.');
            return;
        }

        foreach ($vehicles as $vehicle) {
            $lastTrack = $vehicle->gpsTracks()->latest('recorded_at')->first();

            $baseLat = $lastTrack?->latitude ?? -6.2088;
            $baseLng = $lastTrack?->longitude ?? 106.8456;

            $lat = $baseLat + (mt_rand(-100, 100) / 10000);
            $lng = $baseLng + (mt_rand(-100, 100) / 10000);
            $speed = $vehicle->status === 'in_use' ? mt_rand(20, 60) : 0;
            $heading = mt_rand(0, 359);

            $track = $service->recordGpsTrack($vehicle, $lat, $lng, $speed, $heading);

            $this->line("GPS direkam: {$vehicle->plate_number} @ {$lat}, {$lng}");
        }

        $this->info('Rekam GPS armada selesai.');
    }
}
