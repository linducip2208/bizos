<?php

namespace App\Filament\Resources\PropertyUnits\Pages;

use App\Filament\Concerns\HasBulkActions;
use App\Filament\Concerns\HasExcelExport;
use App\Filament\Resources\PropertyUnits\PropertyUnitResource;
use Filament\Resources\Pages\ListRecords;

class ListPropertyUnits extends ListRecords
{
    use HasBulkActions;
    use HasExcelExport;

    protected static string $resource = PropertyUnitResource::class;
}