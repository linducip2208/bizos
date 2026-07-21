<?php

namespace App\Filament\Resources\CanteenOrders\Pages;

use App\Filament\Resources\CanteenOrders\CanteenOrderResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCanteenOrders extends ListRecords
{
    protected static string $resource = CanteenOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}