<?php

namespace App\Filament\Resources\WaBlastLogs\Pages;

use App\Filament\Resources\WaBlastLogs\WaBlastLogResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditWaBlastLog extends EditRecord
{
    protected static string $resource = WaBlastLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
