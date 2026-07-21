<?php

namespace App\Filament\Resources\SubcontractOrders\Pages;

use App\Filament\Resources\SubcontractOrders\SubcontractOrderResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSubcontractOrders extends ListRecords
{
    protected static string $resource = SubcontractOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}