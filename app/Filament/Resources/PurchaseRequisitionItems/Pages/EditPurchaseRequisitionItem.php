<?php

namespace App\Filament\Resources\PurchaseRequisitionItems\Pages;

use App\Filament\Resources\PurchaseRequisitionItems\PurchaseRequisitionItemResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPurchaseRequisitionItem extends EditRecord
{
    protected static string $resource = PurchaseRequisitionItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}