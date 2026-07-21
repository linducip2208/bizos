<?php

namespace App\Filament\Resources\MeetingAttendees\Pages;

use App\Filament\Resources\MeetingAttendees\MeetingAttendeeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMeetingAttendee extends EditRecord
{
    protected static string $resource = MeetingAttendeeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
