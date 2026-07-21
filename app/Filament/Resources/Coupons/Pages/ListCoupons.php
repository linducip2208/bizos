<?php

namespace App\Filament\Resources\Coupons\Pages;

use App\Filament\Concerns\HasExcelExport;
use App\Filament\Resources\Coupons\CouponResource;
use Filament\Resources\Pages\ListRecords;

class ListCoupons extends ListRecords
{
    use HasExcelExport;

    protected static string $resource = CouponResource::class;
}
