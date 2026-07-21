<?php

namespace App\Filament\Resources\PurchaseOrders\Pages;

use App\Filament\Concerns\HasBulkActions;
use App\Filament\Concerns\HasExcelExport;
use App\Filament\Resources\PurchaseOrders\PurchaseOrderResource;
use Filament\Resources\Pages\ListRecords;

class ListPurchaseOrders extends ListRecords
{
    use HasBulkActions;
    use HasExcelExport;

    protected static string $resource = PurchaseOrderResource::class;
}