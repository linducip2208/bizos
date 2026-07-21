<?php

namespace App\Filament\Resources\DocumentGenerations\Pages;

use App\Filament\Resources\DocumentGenerations\DocumentGenerationResource;
use Filament\Resources\Pages\ListRecords;

class ListDocumentGenerations extends ListRecords
{
    protected static string $resource = DocumentGenerationResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}