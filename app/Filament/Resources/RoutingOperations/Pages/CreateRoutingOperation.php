<?php

namespace App\Filament\Resources\RoutingOperations\Pages;

use App\Filament\Resources\RoutingOperations\RoutingOperationResource;
use Filament\Resources\Pages\CreateRecord;

class CreateRoutingOperation extends CreateRecord
{
    protected static string $resource = RoutingOperationResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}