<?php

namespace App\Filament\Resources\GoodsReceiptItems\Pages;

use App\Filament\Resources\GoodsReceiptItems\GoodsReceiptItemResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditGoodsReceiptItem extends EditRecord
{
    protected static string $resource = GoodsReceiptItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}