<?php

namespace App\Filament\Resources\WasteLogs\Pages;

use App\Filament\Resources\WasteLogs\WasteLogResource;
use Filament\Resources\Pages\CreateRecord;

class CreateWasteLog extends CreateRecord
{
    protected static string $resource = WasteLogResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}