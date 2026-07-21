<?php

namespace App\Filament\Resources\WikiCategoryResource\Pages;

use App\Filament\Resources\WikiCategoryResource\WikiCategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWikiCategories extends ListRecords
{
    protected static string $resource = WikiCategoryResource::class;
    protected function getHeaderActions(): array { return [CreateAction::make()->label('Tambah Kategori')]; }
}
