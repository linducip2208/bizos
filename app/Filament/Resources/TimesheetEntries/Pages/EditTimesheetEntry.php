<?php

namespace App\Filament\Resources\TimesheetEntries\Pages;

use App\Filament\Resources\TimesheetEntries\TimesheetEntryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTimesheetEntry extends EditRecord
{
    protected static string $resource = TimesheetEntryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}