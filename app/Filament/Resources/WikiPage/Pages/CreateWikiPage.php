<?php

namespace App\Filament\Resources\WikiPage\Pages;

use App\Filament\Resources\WikiPage\WikiPageResource;
use Filament\Resources\Pages\CreateRecord;

class CreateWikiPage extends CreateRecord
{
    protected static string $resource = WikiPageResource::class;
    protected function getRedirectUrl(): string { return $this->getResource()::getUrl('index'); }
}