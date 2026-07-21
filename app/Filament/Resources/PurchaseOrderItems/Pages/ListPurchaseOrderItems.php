<?php

namespace App\Filament\Resources\PurchaseOrderItems\Pages;

use App\Filament\Concerns\HasBulkActions;
use App\Filament\Concerns\HasExcelExport;
use App\Filament\Resources\PurchaseOrderItems\PurchaseOrderItemResource;
use Filament\Resources\Pages\ListRecords;

class ListPurchaseOrderItems extends ListRecords
{
    use HasBulkActions;
    use HasExcelExport;

    protected static string $resource = PurchaseOrderItemResource::class;
}