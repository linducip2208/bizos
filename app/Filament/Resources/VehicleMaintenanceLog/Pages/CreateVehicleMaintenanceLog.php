<?php

namespace App\Filament\Resources\VehicleMaintenanceLog\Pages;

use App\Filament\Resources\VehicleMaintenanceLog\VehicleMaintenanceLogResource;
use Filament\Resources\Pages\CreateRecord;

class CreateVehicleMaintenanceLog extends CreateRecord
{
    protected static string $resource = VehicleMaintenanceLogResource::class;
    protected function getRedirectUrl(): string { return $this->getResource()::getUrl('index'); }
}