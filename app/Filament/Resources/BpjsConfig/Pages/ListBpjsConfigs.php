<?php

namespace App\Filament\Resources\BpjsConfig\Pages;

use App\Filament\Resources\BpjsConfig\BpjsConfigResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBpjsConfigs extends ListRecords
{
    protected static string $resource = BpjsConfigResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}