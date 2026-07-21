<?php

namespace App\Filament\Resources\PurchaseRequisitionItems\Pages;

use App\Filament\Concerns\HasBulkActions;
use App\Filament\Concerns\HasExcelExport;
use App\Filament\Resources\PurchaseRequisitionItems\PurchaseRequisitionItemResource;
use Filament\Resources\Pages\ListRecords;

class ListPurchaseRequisitionItems extends ListRecords
{
    use HasBulkActions;
    use HasExcelExport;

    protected static string $resource = PurchaseRequisitionItemResource::class;
}