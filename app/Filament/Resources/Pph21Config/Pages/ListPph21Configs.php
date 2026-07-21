<?php

namespace App\Filament\Resources\Pph21Config\Pages;

use App\Filament\Resources\Pph21Config\Pph21ConfigResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPph21Configs extends ListRecords
{
    protected static string $resource = Pph21ConfigResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}