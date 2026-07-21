<?php

namespace App\Filament\Resources\MeetingMinutes\Pages;

use App\Filament\Resources\MeetingMinutes\MeetingMinuteResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMeetingMinute extends EditRecord
{
    protected static string $resource = MeetingMinuteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
