<?php

namespace App\Filament\Resources\RoutingOperations\Pages;

use App\Filament\Resources\RoutingOperations\RoutingOperationResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditRoutingOperation extends EditRecord
{
    protected static string $resource = RoutingOperationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}