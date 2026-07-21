<?php

namespace App\Filament\Resources\PurchaseRequisitions\Pages;

use App\Filament\Concerns\HasBulkActions;
use App\Filament\Concerns\HasExcelExport;
use App\Filament\Resources\PurchaseRequisitions\PurchaseRequisitionResource;
use App\Models\PurchaseRequisition;
use Filament\Resources\Pages\ListRecords;

class ListPurchaseRequisitions extends ListRecords
{
    use HasBulkActions;
    use HasExcelExport;

    protected static string $resource = PurchaseRequisitionResource::class;

    protected function getCustomBulkActions(): array
    {
        return [];
    }
}
