<?php

namespace App\Filament\Resources\RoutingOperationResource\Pages;

use App\Filament\Resources\RoutingOperationResource;
use Filament\Resources\Pages\CreateRecord;

class CreateRoutingOperation extends CreateRecord
{
    protected static string $resource = RoutingOperationResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
