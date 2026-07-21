<?php

namespace App\Filament\Resources\WikiCategory\Pages;

use App\Filament\Resources\WikiCategory\WikiCategoryResource;
use Filament\Resources\Pages\EditRecord;

class EditWikiCategory extends EditRecord
{
    protected static string $resource = WikiCategoryResource::class;
    protected function getRedirectUrl(): string { return $this->getResource()::getUrl('index'); }
}