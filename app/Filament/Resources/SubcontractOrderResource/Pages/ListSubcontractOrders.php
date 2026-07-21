<?php

namespace App\Filament\Resources\SubcontractOrderResource\Pages;

use App\Filament\Resources\SubcontractOrderResource;
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
