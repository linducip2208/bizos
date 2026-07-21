<?php

namespace App\Filament\Resources\CanteenOrderItems\Pages;

use App\Filament\Resources\CanteenOrderItems\CanteenOrderItemResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCanteenOrderItems extends ListRecords
{
    protected static string $resource = CanteenOrderItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}