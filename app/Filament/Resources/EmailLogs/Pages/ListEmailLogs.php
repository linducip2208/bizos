<?php

namespace App\Filament\Resources\EmailLogs\Pages;

use App\Filament\Concerns\HasExcelExport;
use App\Filament\Resources\EmailLogs\EmailLogResource;
use Filament\Resources\Pages\ListRecords;

class ListEmailLogs extends ListRecords
{
    use HasExcelExport;

    protected static string $resource = EmailLogResource::class;
}
