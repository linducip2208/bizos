<?php

namespace App\Filament\Resources\DeliveryItemResource\Pages;

use App\Filament\Resources\DeliveryItemResource\DeliveryItemResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDeliveryItems extends ListRecords
{
    protected static string $resource = DeliveryItemResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('Tambah Item')];
    }
}
