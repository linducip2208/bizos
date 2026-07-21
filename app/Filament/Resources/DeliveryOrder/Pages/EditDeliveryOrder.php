<?php

namespace App\Filament\Resources\DeliveryOrder\Pages;

use App\Filament\Resources\DeliveryOrder\DeliveryOrderResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDeliveryOrder extends EditRecord
{
    protected static string $resource = DeliveryOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}