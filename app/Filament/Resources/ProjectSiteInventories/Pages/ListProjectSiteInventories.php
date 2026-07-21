<?php

namespace App\Filament\Resources\ProjectSiteInventories\Pages;

use App\Filament\Concerns\HasBulkActions;
use App\Filament\Concerns\HasExcelExport;
use App\Filament\Resources\ProjectSiteInventories\ProjectSiteInventoryResource;
use Filament\Resources\Pages\ListRecords;

class ListProjectSiteInventories extends ListRecords
{
    use HasBulkActions;
    use HasExcelExport;

    protected static string $resource = ProjectSiteInventoryResource::class;
}
