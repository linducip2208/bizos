<?php

namespace App\Filament\Resources\SubcontractOrderResource\Pages;

use App\Filament\Resources\SubcontractOrderResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSubcontractOrder extends CreateRecord
{
    protected static string $resource = SubcontractOrderResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
