<?php

namespace App\Filament\Resources\MeetingMinutes\Pages;

use App\Filament\Resources\MeetingMinutes\MeetingMinuteResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMeetingMinute extends CreateRecord
{
    protected static string $resource = MeetingMinuteResource::class;
}
