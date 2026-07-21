<?php

namespace App\Filament\Resources\ColdChainLogResource\Pages;

use App\Filament\Resources\ColdChainLogResource\ColdChainLogResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditColdChainLog extends EditRecord
{
    protected static string $resource = ColdChainLogResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
