<?php

namespace App\Filament\Resources\CashDenominations\Pages;

use App\Filament\Resources\CashDenominations\CashDenominationResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCashDenomination extends EditRecord
{
    protected static string $resource = CashDenominationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
