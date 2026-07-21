<?php

namespace App\Filament\Resources\SalesOrders\Pages;

use App\Filament\Concerns\HasExcelExport;
use App\Filament\Resources\SalesOrders\SalesOrderResource;
use Filament\Resources\Pages\ListRecords;

class ListSalesOrders extends ListRecords
{
    use HasExcelExport;

    protected static string $resource = SalesOrderResource::class;
}
