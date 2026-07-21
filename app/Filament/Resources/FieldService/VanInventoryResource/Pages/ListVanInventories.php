<?php

namespace App\Filament\Resources\FieldService\VanInventoryResource\Pages;

use App\Filament\Resources\FieldService\VanInventoryResource\VanInventoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListVanInventories extends ListRecords
{
    protected static string $resource = VanInventoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Tambah Stok Van'),
        ];
    }
}