<?php

namespace App\Filament\Resources\CallLogs\Pages;

use App\Filament\Concerns\HasExcelExport;
use App\Filament\Resources\CallLogs\CallLogResource;
use Filament\Resources\Pages\ListRecords;

class ListCallLogs extends ListRecords
{
    use HasExcelExport;

    protected static string $resource = CallLogResource::class;
}
