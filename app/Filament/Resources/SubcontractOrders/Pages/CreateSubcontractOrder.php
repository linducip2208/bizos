<?php

namespace App\Filament\Resources\SubcontractOrders\Pages;

use App\Filament\Resources\SubcontractOrders\SubcontractOrderResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSubcontractOrder extends CreateRecord
{
    protected static string $resource = SubcontractOrderResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}