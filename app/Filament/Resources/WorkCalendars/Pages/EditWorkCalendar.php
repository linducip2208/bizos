<?php

namespace App\Filament\Resources\WorkCalendars\Pages;

use App\Filament\Resources\WorkCalendars\WorkCalendarResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditWorkCalendar extends EditRecord
{
    protected static string $resource = WorkCalendarResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
