<?php

namespace App\Filament\Resources\GoodsReceipts\Pages;

use App\Filament\Concerns\HasBulkActions;
use App\Filament\Concerns\HasExcelExport;
use App\Filament\Resources\GoodsReceipts\GoodsReceiptResource;
use Filament\Resources\Pages\ListRecords;

class ListGoodsReceipts extends ListRecords
{
    use HasBulkActions;
    use HasExcelExport;

    protected static string $resource = GoodsReceiptResource::class;
}