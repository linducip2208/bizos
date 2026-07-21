<?php

namespace App\Filament\Resources\AttendanceConfigs\Pages;

use App\Filament\Resources\AttendanceConfigs\AttendanceConfigResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAttendanceConfig extends EditRecord
{
    protected static string $resource = AttendanceConfigResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}