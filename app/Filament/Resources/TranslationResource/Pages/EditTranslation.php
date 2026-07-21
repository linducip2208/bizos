<?php

namespace App\Filament\Resources\TranslationResource\Pages;

use App\Filament\Resources\TranslationResource\TranslationResource;
use Filament\Resources\Pages\EditRecord;

class EditTranslation extends EditRecord
{
    protected static string $resource = TranslationResource::class;
    protected function getRedirectUrl(): string { return $this->getResource()::getUrl('index'); }
}
