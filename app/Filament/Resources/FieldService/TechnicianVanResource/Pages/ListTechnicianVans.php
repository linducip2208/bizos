<?php

namespace App\Filament\Resources\FieldService\TechnicianVanResource\Pages;

use App\Filament\Resources\FieldService\TechnicianVanResource\TechnicianVanResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTechnicianVans extends ListRecords
{
    protected static string $resource = TechnicianVanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Tambah Van'),
        ];
    }
}