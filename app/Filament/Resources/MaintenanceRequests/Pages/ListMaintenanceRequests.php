<?php

namespace App\Filament\Resources\MaintenanceRequests\Pages;

use App\Filament\Concerns\HasBulkActions;
use App\Filament\Concerns\HasExcelExport;
use App\Filament\Resources\MaintenanceRequests\MaintenanceRequestResource;
use Filament\Resources\Pages\ListRecords;

class ListMaintenanceRequests extends ListRecords
{
    use HasBulkActions;
    use HasExcelExport;

    protected static string $resource = MaintenanceRequestResource::class;
}
