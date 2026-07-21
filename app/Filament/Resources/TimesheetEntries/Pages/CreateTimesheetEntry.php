<?php

namespace App\Filament\Resources\TimesheetEntries\Pages;

use App\Filament\Resources\TimesheetEntries\TimesheetEntryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTimesheetEntry extends CreateRecord
{
    protected static string $resource = TimesheetEntryResource::class;
}
