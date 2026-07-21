<?php

namespace App\Filament\Resources\EcommerceOrder\Pages;

use App\Filament\Resources\EcommerceOrder\EcommerceOrderResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditEcommerceOrder extends EditRecord
{
    protected static string $resource = EcommerceOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}