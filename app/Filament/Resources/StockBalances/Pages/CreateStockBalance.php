<?php

namespace App\Filament\Resources\StockBalances\Pages;

use App\Filament\Resources\StockBalances\StockBalanceResource;
use Filament\Resources\Pages\CreateRecord;

class CreateStockBalance extends CreateRecord
{
    protected static string $resource = StockBalanceResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['company_id'] = auth()->user()->company_id;

        return $data;
    }
}
