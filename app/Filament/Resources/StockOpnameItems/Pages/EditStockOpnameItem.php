<?php

namespace App\Filament\Resources\StockOpnameItems\Pages;

use App\Filament\Resources\StockOpnameItems\StockOpnameItemResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditStockOpnameItem extends EditRecord
{
    protected static string $resource = StockOpnameItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
