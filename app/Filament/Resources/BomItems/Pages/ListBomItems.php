<?php

namespace App\Filament\Resources\BomItems\Pages;

use App\Filament\Resources\BomItems\BomItemResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBomItems extends ListRecords
{
    protected static string $resource = BomItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}