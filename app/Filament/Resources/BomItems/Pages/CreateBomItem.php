<?php

namespace App\Filament\Resources\BomItems\Pages;

use App\Filament\Resources\BomItems\BomItemResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBomItem extends CreateRecord
{
    protected static string $resource = BomItemResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}