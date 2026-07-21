<?php

namespace App\Filament\Resources\PosPayments\Pages;

use App\Filament\Resources\PosPayments\PosPaymentResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePosPayment extends CreateRecord
{
    protected static string $resource = PosPaymentResource::class;
}
