<?php

namespace App\Filament\Resources\StockOpnameItems\Pages;

use App\Filament\Concerns\HasBulkActions;
use App\Filament\Concerns\HasExcelExport;
use App\Filament\Resources\StockOpnameItems\StockOpnameItemResource;
use Filament\Resources\Pages\ListRecords;

class ListStockOpnameItems extends ListRecords
{
    use HasBulkActions;
    use HasExcelExport;

    protected static string $resource = StockOpnameItemResource::class;
}