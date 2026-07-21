<?php

namespace App\Filament\Resources\CanteenOrders\Pages;

use App\Filament\Resources\CanteenOrders\CanteenOrderResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCanteenOrder extends CreateRecord
{
    protected static string $resource = CanteenOrderResource::class;
}
