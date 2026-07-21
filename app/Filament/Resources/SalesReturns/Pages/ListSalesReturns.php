<?php

namespace App\Filament\Resources\SalesReturns\Pages;

use App\Filament\Concerns\HasExcelExport;
use App\Filament\Resources\SalesReturns\SalesReturnResource;
use Filament\Resources\Pages\ListRecords;

class ListSalesReturns extends ListRecords
{
    use HasExcelExport;

    protected static string $resource = SalesReturnResource::class;
}
