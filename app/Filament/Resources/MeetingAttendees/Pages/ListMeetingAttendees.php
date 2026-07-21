<?php

namespace App\Filament\Resources\MeetingAttendees\Pages;

use App\Filament\Resources\MeetingAttendees\MeetingAttendeeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMeetingAttendees extends ListRecords
{
    protected static string $resource = MeetingAttendeeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
