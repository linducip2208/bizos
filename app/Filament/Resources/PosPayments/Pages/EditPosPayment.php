<?php

namespace App\Filament\Resources\PosPayments\Pages;

use App\Filament\Resources\PosPayments\PosPaymentResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPosPayment extends EditRecord
{
    protected static string $resource = PosPaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
