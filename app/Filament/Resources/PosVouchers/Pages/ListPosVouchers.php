<?php

namespace App\Filament\Resources\PosVouchers\Pages;

use App\Filament\Resources\PosVouchers\PosVoucherResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPosVouchers extends ListRecords
{
    protected static string $resource = PosVoucherResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
