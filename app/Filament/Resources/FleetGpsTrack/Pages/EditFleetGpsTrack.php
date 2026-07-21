<?php

namespace App\Filament\Resources\FleetGpsTrack\Pages;

use App\Filament\Resources\FleetGpsTrack\FleetGpsTrackResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditFleetGpsTrack extends EditRecord
{
    protected static string $resource = FleetGpsTrackResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}