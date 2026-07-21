<?php

namespace App\Filament\Resources\FleetGpsTrackResource\Pages;

use App\Filament\Resources\FleetGpsTrackResource\FleetGpsTrackResource;
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
