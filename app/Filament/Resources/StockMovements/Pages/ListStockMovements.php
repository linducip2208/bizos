<?php

namespace App\Filament\Resources\StockMovements\Pages;

use App\Filament\Concerns\HasBulkActions;
use App\Filament\Concerns\HasExcelExport;
use App\Filament\Resources\StockMovements\StockMovementResource;
use Filament\Resources\Pages\ListRecords;

class ListStockMovements extends ListRecords
{
    use HasBulkActions;
    use HasExcelExport;

    protected static string $resource = StockMovementResource::class;
}
