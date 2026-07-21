<?php

namespace App\Filament\Resources\FieldService\ServiceChecklistResource\Pages;

use App\Filament\Resources\FieldService\ServiceChecklistResource\ServiceChecklistResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListServiceChecklists extends ListRecords
{
    protected static string $resource = ServiceChecklistResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Buat Checklist'),
        ];
    }
}
