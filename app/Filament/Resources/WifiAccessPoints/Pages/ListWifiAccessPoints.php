<?php

namespace App\Filament\Resources\WifiAccessPoints\Pages;

use App\Filament\Resources\WifiAccessPoints\WifiAccessPointResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWifiAccessPoints extends ListRecords
{
    protected static string $resource = WifiAccessPointResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}