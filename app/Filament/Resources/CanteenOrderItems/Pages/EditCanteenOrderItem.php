<?php

namespace App\Filament\Resources\CanteenOrderItems\Pages;

use App\Filament\Resources\CanteenOrderItems\CanteenOrderItemResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCanteenOrderItem extends EditRecord
{
    protected static string $resource = CanteenOrderItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
