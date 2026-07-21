<?php

namespace App\Filament\Resources\TaxTransaction\Pages;

use App\Filament\Resources\TaxTransaction\TaxTransactionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTaxTransaction extends CreateRecord
{
    protected static string $resource = TaxTransactionResource::class;
}