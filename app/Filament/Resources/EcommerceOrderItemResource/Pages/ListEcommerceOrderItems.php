<?php

namespace App\Filament\Resources\EcommerceOrderItemResource\Pages;

use App\Filament\Resources\EcommerceOrderItemResource\EcommerceOrderItemResource;
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
