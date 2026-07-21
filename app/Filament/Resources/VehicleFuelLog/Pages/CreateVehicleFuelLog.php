<?php

namespace App\Filament\Resources\VehicleFuelLog\Pages;

use App\Filament\Resources\VehicleFuelLog\VehicleFuelLogResource;
use Filament\Resources\Pages\CreateRecord;

class CreateVehicleFuelLog extends CreateRecord
{
    protected static string $resource = VehicleFuelLogResource::class;
    protected function getRedirectUrl(): string { return $this->getResource()::getUrl('index'); }
}