<?php

namespace App\Filament\Resources\EcommerceOrderResource\Pages;

use App\Filament\Resources\EcommerceOrderResource\EcommerceOrderResource;
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
