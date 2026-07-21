<?php

namespace App\Filament\Resources\EnergyReading\Pages;

use App\Filament\Resources\EnergyReading\EnergyReadingResource;
use Filament\Resources\Pages\ListRecords;

class ListEnergyReadings extends ListRecords
{
    protected static string $resource = EnergyReadingResource::class;
}