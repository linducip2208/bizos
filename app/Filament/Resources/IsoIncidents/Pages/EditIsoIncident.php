<?php

namespace App\Filament\Resources\IsoIncidents\Pages;

use App\Filament\Resources\IsoIncidents\IsoIncidentResource;
use Filament\Resources\Pages\EditRecord;

class EditIsoIncident extends EditRecord
{
    protected static string $resource = IsoIncidentResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}