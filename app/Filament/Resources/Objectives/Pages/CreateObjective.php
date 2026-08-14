<?php

namespace App\Filament\Resources\Objectives\Pages;

use App\Filament\Resources\Objectives\ObjectiveResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateObjective extends CreateRecord
{
    protected static string $resource = ObjectiveResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $data['created_by'] = auth()->id();

        return static::getModel()::create($data);
    }
}
