<?php

namespace App\Filament\Resources\RoutingOperationResource\Pages;

use App\Filament\Resources\RoutingOperationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRoutingOperations extends ListRecords
{
    protected static string $resource = RoutingOperationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
