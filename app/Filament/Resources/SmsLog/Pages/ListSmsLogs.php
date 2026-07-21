<?php

namespace App\Filament\Resources\SmsLog\Pages;

use App\Filament\Resources\SmsLog\SmsLogResource;
use Filament\Resources\Pages\ListRecords;

class ListSmsLogs extends ListRecords
{
    protected static string $resource = SmsLogResource::class;
    protected function getHeaderActions(): array { return []; }
}