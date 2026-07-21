<?php

namespace App\Filament\Resources\FeedbackQuestions\Pages;

use App\Filament\Resources\FeedbackQuestions\FeedbackQuestionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditFeedbackQuestion extends EditRecord
{
    protected static string $resource = FeedbackQuestionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
