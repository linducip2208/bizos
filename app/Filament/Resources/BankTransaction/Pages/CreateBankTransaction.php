<?php

namespace App\Filament\Resources\BankTransaction\Pages;

use App\Filament\Resources\BankTransaction\BankTransactionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBankTransaction extends CreateRecord
{
    protected static string $resource = BankTransactionResource::class;
}