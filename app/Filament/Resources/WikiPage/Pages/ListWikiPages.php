<?php

namespace App\Filament\Resources\WikiPage\Pages;

use App\Filament\Resources\WikiPage\WikiPageResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWikiPages extends ListRecords
{
    protected static string $resource = WikiPageResource::class;
    protected function getHeaderActions(): array { return [CreateAction::make()->label('Tambah Halaman')]; }
}