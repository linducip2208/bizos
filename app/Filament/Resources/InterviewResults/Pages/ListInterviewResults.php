<?php

namespace App\Filament\Resources\InterviewResults\Pages;

use App\Filament\Resources\InterviewResults\InterviewResultResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListInterviewResults extends ListRecords
{
    protected static string $resource = InterviewResultResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}