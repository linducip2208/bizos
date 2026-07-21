<?php

namespace App\Filament\Resources\DataBreaches\Pages;

use App\Filament\Resources\DataBreaches\DataBreachResource;
use Filament\Resources\Pages\ListRecords;

class ListDataBreaches extends ListRecords
{
    protected static string $resource = DataBreachResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
