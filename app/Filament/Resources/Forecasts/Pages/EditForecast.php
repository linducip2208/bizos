<?php

namespace App\Filament\Resources\Forecasts\Pages;

use App\Filament\Resources\Forecasts\ForecastResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditForecast extends EditRecord
{
    protected static string $resource = ForecastResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
