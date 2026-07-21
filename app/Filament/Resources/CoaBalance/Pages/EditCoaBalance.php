<?php

namespace App\Filament\Resources\CoaBalance\Pages;

use App\Filament\Resources\CoaBalance\CoaBalanceResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCoaBalance extends EditRecord
{
    protected static string $resource = CoaBalanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
