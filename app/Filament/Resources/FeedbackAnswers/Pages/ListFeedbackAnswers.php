<?php

namespace App\Filament\Resources\FeedbackAnswers\Pages;

use App\Filament\Resources\FeedbackAnswers\FeedbackAnswerResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFeedbackAnswers extends ListRecords
{
    protected static string $resource = FeedbackAnswerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
