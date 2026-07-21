<?php

namespace App\Filament\Resources\LoyaltyTransactions\Pages;

use App\Filament\Concerns\HasBulkActions;
use App\Filament\Concerns\HasExcelExport;
use App\Filament\Resources\LoyaltyTransactions\LoyaltyTransactionResource;
use Filament\Actions\CreateAction;

class ListLoyaltyTransactions extends ListRecords
{
    use HasExcelExport, HasBulkActions;

    protected static string $resource = LoyaltyTransactionResource::class;

    protected function getCustomHeaderActions(): array
    {
        return [];
    }
}
