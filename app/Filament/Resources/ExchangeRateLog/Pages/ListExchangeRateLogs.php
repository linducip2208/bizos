<?php

namespace App\Filament\Resources\ExchangeRateLog\Pages;

use App\Filament\Resources\ExchangeRateLog\ExchangeRateLogResource;
use Filament\Resources\Pages\ListRecords;

class ListExchangeRateLogs extends ListRecords
{
    protected static string $resource = ExchangeRateLogResource::class;
}