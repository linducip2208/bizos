<?php

namespace App\Filament\Resources\TaxTransaction\Pages;

use App\Filament\Resources\TaxTransaction\TaxTransactionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTaxTransactions extends ListRecords
{
    protected static string $resource = TaxTransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
