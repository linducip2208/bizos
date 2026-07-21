<?php

namespace App\Filament\Resources\IsoPolicies\Pages;

use App\Filament\Resources\IsoPolicies\IsoPolicyResource;
use Filament\Resources\Pages\ListRecords;

class ListIsoPolicies extends ListRecords
{
    protected static string $resource = IsoPolicyResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}