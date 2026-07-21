<?php

namespace App\Filament\Resources\ProductionQcChecks\Pages;

use App\Filament\Resources\ProductionQcChecks\ProductionQcCheckResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProductionQcCheck extends CreateRecord
{
    protected static string $resource = ProductionQcCheckResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}