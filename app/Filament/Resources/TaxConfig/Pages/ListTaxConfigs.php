<?php

namespace App\Filament\Resources\TaxConfig\Pages;

use App\Filament\Resources\TaxConfig\TaxConfigResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTaxConfigs extends ListRecords
{
    protected static string $resource = TaxConfigResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
