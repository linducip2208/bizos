<?php

namespace App\Filament\Resources\PosTransactionItems\Pages;

use App\Filament\Resources\PosTransactionItems\PosTransactionItemResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePosTransactionItem extends CreateRecord
{
    protected static string $resource = PosTransactionItemResource::class;
}