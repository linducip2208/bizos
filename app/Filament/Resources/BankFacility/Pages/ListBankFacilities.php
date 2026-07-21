<?php

namespace App\Filament\Resources\BankFacility\Pages;

use App\Filament\Resources\BankFacility\BankFacilityResource;
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