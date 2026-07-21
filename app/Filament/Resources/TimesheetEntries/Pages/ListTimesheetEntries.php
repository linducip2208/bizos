<?php

namespace App\Filament\Resources\TimesheetEntries\Pages;

use App\Filament\Resources\TimesheetEntries\TimesheetEntryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTimesheetEntries extends ListRecords
{
    protected static string $resource = TimesheetEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}