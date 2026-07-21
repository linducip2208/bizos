<?php

namespace App\Filament\Resources\PosVouchers\Pages;

use App\Filament\Resources\PosVouchers\PosVoucherResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePosVoucher extends CreateRecord
{
    protected static string $resource = PosVoucherResource::class;
}