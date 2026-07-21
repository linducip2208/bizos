<?php

namespace App\Filament\Resources\TaxConfig\Pages;

use App\Filament\Resources\TaxConfig\TaxConfigResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTaxConfig extends EditRecord
{
    protected static string $resource = TaxConfigResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}