<?php

namespace App\Filament\Resources\OnboardingChecklists\Pages;

use App\Filament\Resources\OnboardingChecklists\OnboardingChecklistResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditOnboardingChecklist extends EditRecord
{
    protected static string $resource = OnboardingChecklistResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
