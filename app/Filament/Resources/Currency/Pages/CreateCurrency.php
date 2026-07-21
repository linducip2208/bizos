<?php

namespace App\Filament\Resources\Currency\Pages;

use App\Filament\Resources\Currency\CurrencyResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCurrency extends CreateRecord
{
    protected static string $resource = CurrencyResource::class;
}