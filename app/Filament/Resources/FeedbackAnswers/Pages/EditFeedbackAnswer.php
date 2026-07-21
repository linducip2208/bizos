<?php

namespace App\Filament\Resources\FeedbackAnswers\Pages;

use App\Filament\Resources\FeedbackAnswers\FeedbackAnswerResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditFeedbackAnswer extends EditRecord
{
    protected static string $resource = FeedbackAnswerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
