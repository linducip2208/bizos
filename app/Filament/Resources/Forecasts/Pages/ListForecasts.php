<?php

namespace App\Filament\Resources\Forecasts\Pages;

use App\Filament\Resources\Forecasts\ForecastResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListForecasts extends ListRecords
{
    protected static string $resource = ForecastResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
