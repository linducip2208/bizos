<?php

namespace App\Filament\Resources\DataMergeLogs\Pages;

use App\Filament\Resources\DataMergeLogs\DataMergeLogResource;
use Filament\Resources\Pages\ListRecords;

class ListDataMergeLogs extends ListRecords
{
    protected static string $resource = DataMergeLogResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
