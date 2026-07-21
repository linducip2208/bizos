<?php

namespace App\Filament\Resources\Warehouse\Pages;

use App\Filament\Concerns\HasBulkActions;
use App\Filament\Concerns\HasExcelExport;
use App\Filament\Resources\Warehouse\WarehouseResource;
use Filament\Resources\Pages\ListRecords;

class ListWarehouses extends ListRecords
{
    use HasBulkActions;
    use HasExcelExport;

    protected static string $resource = WarehouseResource::class;
}
