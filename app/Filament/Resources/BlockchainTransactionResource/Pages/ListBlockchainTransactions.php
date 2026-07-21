<?php

namespace App\Filament\Resources\BlockchainTransactionResource\Pages;

use App\Filament\Resources\BlockchainTransactionResource\BlockchainTransactionResource;
use Filament\Resources\Pages\ListRecords;

class ListBlockchainTransactions extends ListRecords
{
    protected static string $resource = BlockchainTransactionResource::class;
}
