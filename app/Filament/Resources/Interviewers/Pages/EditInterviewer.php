<?php

namespace App\Filament\Resources\Interviewers\Pages;

use App\Filament\Resources\Interviewers\InterviewerResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditInterviewer extends EditRecord
{
    protected static string $resource = InterviewerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
