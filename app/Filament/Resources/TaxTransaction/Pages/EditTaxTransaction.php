<?php

namespace App\Filament\Resources\TaxTransaction\Pages;

use App\Filament\Resources\TaxTransaction\TaxTransactionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTaxTransaction extends EditRecord
{
    protected static string $resource = TaxTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
