<?php

namespace App\Filament\Resources\PaymentMethod\Pages;

use App\Filament\Resources\PaymentMethod\PaymentMethodResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePaymentMethod extends CreateRecord
{
    protected static string $resource = PaymentMethodResource::class;
}
