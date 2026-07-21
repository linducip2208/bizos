<?php

namespace App\Filament\Resources\FeedbackReviewers\Pages;

use App\Filament\Resources\FeedbackReviewers\FeedbackReviewerResource;
use Filament\Resources\Pages\CreateRecord;

class CreateFeedbackReviewer extends CreateRecord
{
    protected static string $resource = FeedbackReviewerResource::class;
}