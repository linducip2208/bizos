<?php

namespace App\Filament\Resources\WikiCategoryResource\Pages;

use App\Filament\Resources\WikiCategoryResource\WikiCategoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateWikiCategory extends CreateRecord
{
    protected static string $resource = WikiCategoryResource::class;
    protected function getRedirectUrl(): string { return $this->getResource()::getUrl('index'); }
}
