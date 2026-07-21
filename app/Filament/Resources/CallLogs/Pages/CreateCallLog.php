<?php

namespace App\Filament\Resources\CallLogs\Pages;

use App\Filament\Resources\CallLogs\CallLogResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCallLog extends CreateRecord
{
    protected static string $resource = CallLogResource::class;
}
