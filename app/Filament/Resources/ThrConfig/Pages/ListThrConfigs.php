<?php

namespace App\Filament\Resources\ThrConfig\Pages;

use App\Filament\Resources\ThrConfig\ThrConfigResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListThrConfigs extends ListRecords
{
    protected static string $resource = ThrConfigResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
