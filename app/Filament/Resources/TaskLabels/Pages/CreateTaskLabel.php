<?php

namespace App\Filament\Resources\TaskLabels\Pages;

use App\Filament\Resources\TaskLabels\TaskLabelResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTaskLabel extends CreateRecord
{
    protected static string $resource = TaskLabelResource::class;
}
