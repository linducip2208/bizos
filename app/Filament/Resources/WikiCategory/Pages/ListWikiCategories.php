<?php

namespace App\Filament\Resources\WikiCategory\Pages;

use App\Filament\Resources\WikiCategory\WikiCategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWikiCategories extends ListRecords
{
    protected static string $resource = WikiCategoryResource::class;
    protected function getHeaderActions(): array { return [CreateAction::make()->label('Tambah Kategori')]; }
}