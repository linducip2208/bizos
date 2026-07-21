<?php

namespace App\Filament\Resources\WasteLogResource\Pages;

use App\Filament\Resources\WasteLogResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditWasteLog extends EditRecord
{
    protected static string $resource = WasteLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
