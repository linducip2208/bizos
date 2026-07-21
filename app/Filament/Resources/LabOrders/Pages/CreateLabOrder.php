<?php

namespace App\Filament\Resources\LabOrders\Pages;

use App\Filament\Resources\LabOrders\LabOrderResource;
use Filament\Resources\Pages\CreateRecord;

class CreateLabOrder extends CreateRecord
{
    protected static string $resource = LabOrderResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}