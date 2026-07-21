<?php

namespace App\Filament\Resources\CanteenOrders\Pages;

use App\Filament\Resources\CanteenOrders\CanteenOrderResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCanteenOrder extends EditRecord
{
    protected static string $resource = CanteenOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
