<?php

namespace App\Filament\Resources\PosRefunds\Pages;

use App\Filament\Resources\PosRefunds\PosRefundResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPosRefund extends EditRecord
{
    protected static string $resource = PosRefundResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
