<?php

namespace App\Filament\Resources\FeedbackCycles\Pages;

use App\Filament\Resources\FeedbackCycles\FeedbackCycleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFeedbackCycles extends ListRecords
{
    protected static string $resource = FeedbackCycleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
