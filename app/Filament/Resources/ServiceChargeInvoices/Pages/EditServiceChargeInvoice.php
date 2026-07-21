<?php

namespace App\Filament\Resources\ServiceChargeInvoices\Pages;

use App\Filament\Resources\ServiceChargeInvoices\ServiceChargeInvoiceResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditServiceChargeInvoice extends EditRecord
{
    protected static string $resource = ServiceChargeInvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
