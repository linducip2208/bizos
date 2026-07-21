<?php

namespace App\Filament\Resources\PosTransactionItems\Pages;

use App\Filament\Resources\PosTransactionItems\PosTransactionItemResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPosTransactionItem extends EditRecord
{
    protected static string $resource = PosTransactionItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
