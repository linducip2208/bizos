<?php

namespace App\Filament\Resources\BankTransfer\Pages;

use App\Filament\Resources\BankTransfer\BankTransferResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBankTransfer extends CreateRecord
{
    protected static string $resource = BankTransferResource::class;
}