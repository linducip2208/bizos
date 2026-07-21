<?php

namespace App\Filament\Resources\ClientSegments\Pages;

use App\Filament\Resources\ClientSegments\ClientSegmentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListClientSegments extends ListRecords
{
    protected static string $resource = ClientSegmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}