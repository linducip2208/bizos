<?php

namespace App\Filament\Resources\EcommerceOrderItem\Pages;

use App\Filament\Resources\EcommerceOrderItem\EcommerceOrderItemResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEcommerceOrderItems extends ListRecords
{
    protected static string $resource = EcommerceOrderItemResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('Tambah Item')];
    }
}