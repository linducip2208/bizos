<?php

namespace App\Filament\Resources\BankAccount\Pages;

use App\Filament\Resources\BankAccount\BankAccountResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBankAccount extends CreateRecord
{
    protected static string $resource = BankAccountResource::class;
}