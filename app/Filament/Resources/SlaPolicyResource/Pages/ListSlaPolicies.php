<?php

namespace App\Filament\Resources\SlaPolicyResource\Pages;

use App\Filament\Resources\SlaPolicyResource\SlaPolicyResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSlaPolicies extends ListRecords
{
    protected static string $resource = SlaPolicyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
