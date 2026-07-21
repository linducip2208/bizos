<?php

namespace App\Filament\Resources\ProjectPhases\Pages;

use App\Filament\Resources\ProjectPhases\ProjectPhaseResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditProjectPhase extends EditRecord
{
    protected static string $resource = ProjectPhaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}