<?php

namespace App\Filament\Resources\AttendanceConfigs\Pages;

use App\Filament\Resources\AttendanceConfigs\AttendanceConfigResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAttendanceConfig extends CreateRecord
{
    protected static string $resource = AttendanceConfigResource::class;
}