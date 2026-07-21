<?php

namespace App\Filament\Resources\IotReading\Pages;

use App\Filament\Resources\IotReading\IotReadingResource;
use Filament\Resources\Pages\ListRecords;

class ListIotReadings extends ListRecords
{
    protected static string $resource = IotReadingResource::class;
}