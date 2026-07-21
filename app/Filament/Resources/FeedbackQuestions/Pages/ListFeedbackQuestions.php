<?php

namespace App\Filament\Resources\FeedbackQuestions\Pages;

use App\Filament\Resources\FeedbackQuestions\FeedbackQuestionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFeedbackQuestions extends ListRecords
{
    protected static string $resource = FeedbackQuestionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
