<?php

namespace App\Filament\Resources\DailySiteReports\Pages;

use App\Filament\Concerns\HasBulkActions;
use App\Filament\Concerns\HasExcelExport;
use App\Filament\Resources\DailySiteReports\DailySiteReportResource;
use Filament\Resources\Pages\ListRecords;

class ListDailySiteReports extends ListRecords
{
    use HasBulkActions;
    use HasExcelExport;

    protected static string $resource = DailySiteReportResource::class;
}