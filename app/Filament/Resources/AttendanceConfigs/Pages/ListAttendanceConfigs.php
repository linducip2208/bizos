<?php

namespace App\Filament\Resources\AttendanceConfigs\Pages;

use App\Filament\Resources\AttendanceConfigs\AttendanceConfigResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAttendanceConfigs extends ListRecords
{
    protected static string $resource = AttendanceConfigResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
