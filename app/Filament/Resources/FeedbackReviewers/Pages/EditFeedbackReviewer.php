<?php

namespace App\Filament\Resources\FeedbackReviewers\Pages;

use App\Filament\Resources\FeedbackReviewers\FeedbackReviewerResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditFeedbackReviewer extends EditRecord
{
    protected static string $resource = FeedbackReviewerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}