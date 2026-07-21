<?php

namespace App\Filament\Resources\Attendances\Pages;

use App\Filament\Concerns\HasExcelExport;
use App\Filament\Resources\Attendances\AttendanceResource;
use Filament\Resources\Pages\ListRecords;

class ListAttendances extends ListRecords
{
    use HasExcelExport;

    protected static string $resource = AttendanceResource::class;
}
