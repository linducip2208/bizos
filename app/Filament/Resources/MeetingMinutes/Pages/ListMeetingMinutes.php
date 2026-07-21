<?php

namespace App\Filament\Resources\MeetingMinutes\Pages;

use App\Filament\Resources\MeetingMinutes\MeetingMinuteResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMeetingMinutes extends ListRecords
{
    protected static string $resource = MeetingMinuteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
