<?php

namespace App\Filament\Resources\MeetingAttendees\Pages;

use App\Filament\Resources\MeetingAttendees\MeetingAttendeeResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMeetingAttendee extends CreateRecord
{
    protected static string $resource = MeetingAttendeeResource::class;
}
