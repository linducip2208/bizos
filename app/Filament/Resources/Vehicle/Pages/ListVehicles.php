<?php

namespace App\Filament\Resources\Vehicle\Pages;

use App\Filament\Resources\Vehicle\VehicleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListVehicles extends ListRecords
{
    protected static string $resource = VehicleResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('Tambah Kendaraan')];
    }
}