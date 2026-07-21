<?php

namespace App\Filament\Resources\GoodsReceiptItems\Pages;

use App\Filament\Concerns\HasBulkActions;
use App\Filament\Concerns\HasExcelExport;
use App\Filament\Resources\GoodsReceiptItems\GoodsReceiptItemResource;
use Filament\Resources\Pages\ListRecords;

class ListGoodsReceiptItems extends ListRecords
{
    use HasBulkActions;
    use HasExcelExport;

    protected static string $resource = GoodsReceiptItemResource::class;
}