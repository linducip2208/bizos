<?php

namespace App\Filament\Resources\BlockchainTransaction\Pages;

use App\Filament\Resources\BlockchainTransaction\BlockchainTransactionResource;
use Filament\Resources\Pages\ListRecords;

class ListBlockchainTransactions extends ListRecords
{
    protected static string $resource = BlockchainTransactionResource::class;
}