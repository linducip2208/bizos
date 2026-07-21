<?php

namespace App\Filament\Resources\PaySlip\Pages;

use App\Filament\Resources\PaySlip\PaySlipResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPaySlip extends EditRecord
{
    protected static string $resource = PaySlipResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}