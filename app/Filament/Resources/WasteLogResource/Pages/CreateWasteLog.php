<?php

namespace App\Filament\Resources\WasteLogResource\Pages;

use App\Filament\Resources\WasteLogResource;
use Filament\Resources\Pages\CreateRecord;

class CreateWasteLog extends CreateRecord
{
    protected static string $resource = WasteLogResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
