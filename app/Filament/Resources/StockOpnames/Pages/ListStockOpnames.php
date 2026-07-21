<?php

namespace App\Filament\Resources\StockOpnames\Pages;

use App\Filament\Concerns\HasBulkActions;
use App\Filament\Concerns\HasExcelExport;
use App\Filament\Resources\StockOpnames\StockOpnameResource;
use Filament\Resources\Pages\ListRecords;

class ListStockOpnames extends ListRecords
{
    use HasBulkActions;
    use HasExcelExport;

    protected static string $resource = StockOpnameResource::class;
}