<?php

namespace App\Filament\Resources\PosVouchers\Pages;

use App\Filament\Resources\PosVouchers\PosVoucherResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPosVoucher extends EditRecord
{
    protected static string $resource = PosVoucherResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
