<?php

namespace App\Filament\Resources\TaskLabels\Pages;

use App\Filament\Resources\TaskLabels\TaskLabelResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTaskLabels extends ListRecords
{
    protected static string $resource = TaskLabelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
