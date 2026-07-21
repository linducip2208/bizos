<?php

namespace App\Filament\Resources\EcommerceOrderItem\Pages;

use App\Filament\Resources\EcommerceOrderItem\EcommerceOrderItemResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditEcommerceOrderItem extends EditRecord
{
    protected static string $resource = EcommerceOrderItemResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}