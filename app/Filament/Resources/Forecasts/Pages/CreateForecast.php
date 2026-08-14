<?php

namespace App\Filament\Resources\Forecasts\Pages;

use App\Filament\Resources\Forecasts\ForecastResource;
use Filament\Resources\Pages\CreateRecord;

class CreateForecast extends CreateRecord
{
    protected static string $resource = ForecastResource::class;
}
