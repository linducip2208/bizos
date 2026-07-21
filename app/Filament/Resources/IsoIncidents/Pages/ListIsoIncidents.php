<?php

namespace App\Filament\Resources\IsoIncidents\Pages;

use App\Filament\Resources\IsoIncidents\IsoIncidentResource;
use Filament\Resources\Pages\ListRecords;

class ListIsoIncidents extends ListRecords
{
    protected static string $resource = IsoIncidentResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
