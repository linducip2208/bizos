<?php

namespace App\Filament\Resources\Supplier\Pages;

use App\Filament\Concerns\HasBulkActions;
use App\Filament\Concerns\HasExcelExport;
use App\Filament\Resources\Supplier\SupplierResource;
use Filament\Resources\Pages\ListRecords;

class ListSuppliers extends ListRecords
{
    use HasBulkActions;
    use HasExcelExport;

    protected static string $resource = SupplierResource::class;
}