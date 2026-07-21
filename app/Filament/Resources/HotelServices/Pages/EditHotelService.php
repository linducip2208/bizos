<?php

namespace App\Filament\Resources\HotelServices\Pages;

use App\Filament\Resources\HotelServices\HotelServiceResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditHotelService extends EditRecord
{
    protected static string $resource = HotelServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}