<?php

namespace App\Filament\Resources\ReceiptLayouts\Pages;

use App\Filament\Resources\ReceiptLayouts\ReceiptLayoutResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditReceiptLayout extends EditRecord
{
    protected static string $resource = ReceiptLayoutResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
