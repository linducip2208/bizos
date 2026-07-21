<?php

namespace App\Filament\Resources\ProductionQcChecks\Pages;

use App\Filament\Resources\ProductionQcChecks\ProductionQcCheckResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditProductionQcCheck extends EditRecord
{
    protected static string $resource = ProductionQcCheckResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}