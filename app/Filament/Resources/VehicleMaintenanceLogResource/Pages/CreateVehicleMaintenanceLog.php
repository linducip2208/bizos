<?php

namespace App\Filament\Resources\VehicleMaintenanceLogResource\Pages;

use App\Filament\Resources\VehicleMaintenanceLogResource\VehicleMaintenanceLogResource;
use Filament\Resources\Pages\CreateRecord;

class CreateVehicleMaintenanceLog extends CreateRecord
{
    protected static string $resource = VehicleMaintenanceLogResource::class;
    protected function getRedirectUrl(): string { return $this->getResource()::getUrl('index'); }
}
