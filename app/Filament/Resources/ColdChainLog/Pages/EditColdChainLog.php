<?php

namespace App\Filament\Resources\ColdChainLog\Pages;

use App\Filament\Resources\ColdChainLog\ColdChainLogResource;
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