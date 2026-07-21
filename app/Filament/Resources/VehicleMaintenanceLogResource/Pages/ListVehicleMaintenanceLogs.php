<?php

namespace App\Filament\Resources\VehicleMaintenanceLogResource\Pages;

use App\Filament\Resources\VehicleMaintenanceLogResource\VehicleMaintenanceLogResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListVehicleMaintenanceLogs extends ListRecords
{
    protected static string $resource = VehicleMaintenanceLogResource::class;
    protected function getHeaderActions(): array { return [CreateAction::make()->label('Tambah Log Perawatan')]; }
}
