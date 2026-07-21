<?php

namespace App\Filament\Resources\ProductionQcCheckResource\Pages;

use App\Filament\Resources\ProductionQcCheckResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProductionQcCheck extends CreateRecord
{
    protected static string $resource = ProductionQcCheckResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
