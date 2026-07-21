<?php

namespace App\Filament\Resources\QualityChecks\Pages;

use App\Filament\Concerns\HasBulkActions;
use App\Filament\Concerns\HasExcelExport;
use App\Filament\Resources\QualityChecks\QualityCheckResource;
use Filament\Resources\Pages\ListRecords;

class ListQualityChecks extends ListRecords
{
    use HasExcelExport, HasBulkActions;

    protected static string $resource = QualityCheckResource::class;
}
