<?php

namespace App\Filament\Resources\FeedbackQuestions\Pages;

use App\Filament\Resources\FeedbackQuestions\FeedbackQuestionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateFeedbackQuestion extends CreateRecord
{
    protected static string $resource = FeedbackQuestionResource::class;
}