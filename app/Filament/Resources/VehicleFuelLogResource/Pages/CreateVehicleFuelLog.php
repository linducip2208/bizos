<?php

namespace App\Filament\Resources\VehicleFuelLogResource\Pages;

use App\Filament\Resources\VehicleFuelLogResource\VehicleFuelLogResource;
use Filament\Resources\Pages\CreateRecord;

class CreateVehicleFuelLog extends CreateRecord
{
    protected static string $resource = VehicleFuelLogResource::class;
    protected function getRedirectUrl(): string { return $this->getResource()::getUrl('index'); }
}
