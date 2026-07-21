<?php

namespace App\Filament\Resources\FleetGpsTrack\Pages;

use App\Filament\Resources\FleetGpsTrack\FleetGpsTrackResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFleetGpsTracks extends ListRecords
{
    protected static string $resource = FleetGpsTrackResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('Tambah Track')];
    }
}