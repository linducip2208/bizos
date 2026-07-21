<?php

namespace App\Filament\Resources\TaskLabels\Pages;

use App\Filament\Resources\TaskLabels\TaskLabelResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTaskLabel extends EditRecord
{
    protected static string $resource = TaskLabelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}