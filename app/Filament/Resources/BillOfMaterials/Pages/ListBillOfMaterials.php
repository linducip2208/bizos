<?php

namespace App\Filament\Resources\BillOfMaterials\Pages;

use App\Filament\Resources\BillOfMaterials\BillOfMaterialResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBillOfMaterials extends ListRecords
{
    protected static string $resource = BillOfMaterialResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}