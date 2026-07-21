<?php

namespace App\Filament\Resources\FleetGpsTrackResource\Pages;

use App\Filament\Resources\FleetGpsTrackResource\FleetGpsTrackResource;
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
