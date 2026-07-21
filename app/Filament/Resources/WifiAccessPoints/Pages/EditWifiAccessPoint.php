<?php

namespace App\Filament\Resources\WifiAccessPoints\Pages;

use App\Filament\Resources\WifiAccessPoints\WifiAccessPointResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditWifiAccessPoint extends EditRecord
{
    protected static string $resource = WifiAccessPointResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}