<?php

namespace App\Filament\Resources\OnboardingChecklists\Pages;

use App\Filament\Resources\OnboardingChecklists\OnboardingChecklistResource;
use Filament\Resources\Pages\CreateRecord;

class CreateOnboardingChecklist extends CreateRecord
{
    protected static string $resource = OnboardingChecklistResource::class;
}
