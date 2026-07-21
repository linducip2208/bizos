<?php

namespace App\Filament\Resources\FieldService\WorkOrderResource\Pages;

use App\Filament\Resources\FieldService\WorkOrderResource\WorkOrderResource;
use Filament\Resources\Pages\CreateRecord;

class CreateWorkOrder extends CreateRecord
{
    protected static string $resource = WorkOrderResource::class;
}