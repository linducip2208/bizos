<?php

namespace App\Filament\Resources\DeliveryItem\Pages;

use App\Filament\Resources\DeliveryItem\DeliveryItemResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDeliveryItem extends EditRecord
{
    protected static string $resource = DeliveryItemResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}