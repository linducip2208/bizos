<?php

namespace App\Filament\Resources\SalesTargets\Pages;

use App\Filament\Concerns\HasExcelExport;
use App\Filament\Resources\SalesTargets\SalesTargetResource;
use Filament\Resources\Pages\ListRecords;

class ListSalesTargets extends ListRecords
{
    use HasExcelExport;

    protected static string $resource = SalesTargetResource::class;
}
