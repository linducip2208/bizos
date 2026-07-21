<?php

namespace App\Filament\Resources\BankReconciliationResource\Pages;

use App\Filament\Resources\BankReconciliationResource\BankReconciliationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBankReconciliations extends ListRecords
{
    protected static string $resource = BankReconciliationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
