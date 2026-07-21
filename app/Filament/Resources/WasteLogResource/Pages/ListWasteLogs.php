<?php

namespace App\Filament\Resources\WasteLogResource\Pages;

use App\Filament\Resources\WasteLogResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWasteLogs extends ListRecords
{
    protected static string $resource = WasteLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
