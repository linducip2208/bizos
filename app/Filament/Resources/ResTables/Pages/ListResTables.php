<?php

namespace App\Filament\Resources\ResTables\Pages;

use App\Filament\Resources\ResTables\ResTableResource;
use Filament\Resources\Pages\ListRecords;

class ListResTables extends ListRecords
{
    protected static string $resource = ResTableResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
