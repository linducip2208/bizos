<?php

namespace App\Filament\Resources\BankFacilityResource\Pages;

use App\Filament\Resources\BankFacilityResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBankFacilities extends ListRecords
{
    protected static string $resource = BankFacilityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
