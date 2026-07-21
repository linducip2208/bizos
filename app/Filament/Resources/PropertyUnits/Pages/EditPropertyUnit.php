<?php

namespace App\Filament\Resources\PropertyUnits\Pages;

use App\Filament\Resources\PropertyUnits\PropertyUnitResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPropertyUnit extends EditRecord
{
    protected static string $resource = PropertyUnitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
