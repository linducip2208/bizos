<?php

namespace App\Filament\Resources\Sprints\Pages;

use App\Filament\Concerns\HasBulkActions;
use App\Filament\Concerns\HasExcelExport;
use App\Filament\Resources\Sprints\SprintResource;
use Filament\Resources\Pages\ListRecords;

class ListSprints extends ListRecords
{
    use HasExcelExport, HasBulkActions;

    protected static string $resource = SprintResource::class;
}
