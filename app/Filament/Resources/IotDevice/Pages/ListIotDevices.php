<?php

namespace App\Filament\Resources\IotDevice\Pages;

use App\Filament\Resources\IotDevice\IotDeviceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListIotDevices extends ListRecords
{
    protected static string $resource = IotDeviceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}