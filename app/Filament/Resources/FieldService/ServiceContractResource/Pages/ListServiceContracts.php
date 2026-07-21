<?php

namespace App\Filament\Resources\FieldService\ServiceContractResource\Pages;

use App\Filament\Resources\FieldService\ServiceContractResource\ServiceContractResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListServiceContracts extends ListRecords
{
    protected static string $resource = ServiceContractResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Buat Kontrak'),
        ];
    }
}