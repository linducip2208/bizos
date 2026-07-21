<?php

namespace App\Filament\Resources\PosTransactions\Pages;

use App\Filament\Resources\PosTransactions\PosTransactionResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePosTransaction extends CreateRecord
{
    protected static string $resource = PosTransactionResource::class;
}