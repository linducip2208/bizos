<?php

namespace App\Filament\Resources\FieldService\ContractedEquipmentResource\Pages;

use App\Filament\Resources\FieldService\ContractedEquipmentResource\ContractedEquipmentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListContractedEquipment extends ListRecords
{
    protected static string $resource = ContractedEquipmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Tambah Peralatan'),
        ];
    }
}