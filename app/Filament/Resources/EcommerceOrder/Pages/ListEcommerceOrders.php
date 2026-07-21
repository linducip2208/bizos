<?php

namespace App\Filament\Resources\EcommerceOrder\Pages;

use App\Filament\Resources\EcommerceOrder\EcommerceOrderResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEcommerceOrders extends ListRecords
{
    protected static string $resource = EcommerceOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('Tambah Pesanan')];
    }
}