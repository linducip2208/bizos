<?php

namespace App\Filament\Resources\ProductionQcCheckResource\Pages;

use App\Filament\Resources\ProductionQcCheckResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListProductionQcChecks extends ListRecords
{
    protected static string $resource = ProductionQcCheckResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
