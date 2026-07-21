<?php

namespace App\Filament\Resources\FeedbackAnswers\Pages;

use App\Filament\Resources\FeedbackAnswers\FeedbackAnswerResource;
use Filament\Resources\Pages\CreateRecord;

class CreateFeedbackAnswer extends CreateRecord
{
    protected static string $resource = FeedbackAnswerResource::class;
}
