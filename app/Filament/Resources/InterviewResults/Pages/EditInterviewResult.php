<?php

namespace App\Filament\Resources\InterviewResults\Pages;

use App\Filament\Resources\InterviewResults\InterviewResultResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditInterviewResult extends EditRecord
{
    protected static string $resource = InterviewResultResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}