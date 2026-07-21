<?php

namespace App\Filament\Resources\WaBlastLogs\Pages;

use App\Filament\Resources\WaBlastLogs\WaBlastLogResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWaBlastLogs extends ListRecords
{
    protected static string $resource = WaBlastLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}