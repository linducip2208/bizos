<?php

namespace App\Filament\Resources\EnergyMeter\Pages;

use App\Filament\Resources\EnergyMeter\EnergyMeterResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEnergyMeters extends ListRecords
{
    protected static string $resource = EnergyMeterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}