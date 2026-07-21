<?php

namespace App\Filament\Resources\FeedbackCycles\Pages;

use App\Filament\Resources\FeedbackCycles\FeedbackCycleResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditFeedbackCycle extends EditRecord
{
    protected static string $resource = FeedbackCycleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}