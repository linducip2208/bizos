<?php

namespace App\Filament\Resources\GuestFolios\Pages;

use App\Filament\Resources\GuestFolios\GuestFolioResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditGuestFolio extends EditRecord
{
    protected static string $resource = GuestFolioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}