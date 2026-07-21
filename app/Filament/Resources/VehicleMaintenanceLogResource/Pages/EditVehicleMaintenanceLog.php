<?php

namespace App\Filament\Resources\VehicleMaintenanceLogResource\Pages;

use App\Filament\Resources\VehicleMaintenanceLogResource\VehicleMaintenanceLogResource;
use Filament\Resources\Pages\EditRecord;

class EditVehicleMaintenanceLog extends EditRecord
{
    protected static string $resource = VehicleMaintenanceLogResource::class;
    protected function getRedirectUrl(): string { return $this->getResource()::getUrl('index'); }
}
