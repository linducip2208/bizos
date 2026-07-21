<?php

namespace App\Filament\Resources\FieldService\WorkOrderResource\Pages;

use App\Filament\Resources\FieldService\WorkOrderResource\WorkOrderResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWorkOrders extends ListRecords
{
    protected static string $resource = WorkOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Buat Work Order'),
        ];
    }
}
