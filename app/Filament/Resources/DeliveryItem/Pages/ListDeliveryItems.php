<?php

namespace App\Filament\Resources\DeliveryItem\Pages;

use App\Filament\Resources\DeliveryItem\DeliveryItemResource;
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