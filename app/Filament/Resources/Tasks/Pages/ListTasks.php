<?php

namespace App\Filament\Resources\Tasks\Pages;

use App\Filament\Concerns\HasExcelExport;
use App\Filament\Resources\Tasks\TaskResource;
use Filament\Resources\Pages\ListRecords;

class ListTasks extends ListRecords
{
    use HasExcelExport;

    protected static string $resource = TaskResource::class;
}