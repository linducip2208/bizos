<?php

namespace App\Filament\Resources\FeedbackReviewers\Pages;

use App\Filament\Resources\FeedbackReviewers\FeedbackReviewerResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFeedbackReviewers extends ListRecords
{
    protected static string $resource = FeedbackReviewerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
