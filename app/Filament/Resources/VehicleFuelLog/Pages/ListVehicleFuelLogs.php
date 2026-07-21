<?php

namespace App\Filament\Resources\VehicleFuelLog\Pages;

use App\Filament\Resources\VehicleFuelLog\VehicleFuelLogResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListVehicleFuelLogs extends ListRecords
{
    protected static string $resource = VehicleFuelLogResource::class;
    protected function getHeaderActions(): array { return [CreateAction::make()->label('Tambah Log BBM')]; }
}