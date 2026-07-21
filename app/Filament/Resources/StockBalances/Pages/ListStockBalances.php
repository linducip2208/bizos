<?php

namespace App\Filament\Resources\StockBalances\Pages;

use App\Filament\Concerns\HasBulkActions;
use App\Filament\Concerns\HasExcelExport;
use App\Filament\Resources\StockBalances\StockBalanceResource;
use Filament\Resources\Pages\ListRecords;

class ListStockBalances extends ListRecords
{
    use HasBulkActions;
    use HasExcelExport;

    protected static string $resource = StockBalanceResource::class;
}
