<?php

namespace App\Filament\Resources\OnboardingChecklists\Pages;

use App\Filament\Resources\OnboardingChecklists\OnboardingChecklistResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListOnboardingChecklists extends ListRecords
{
    protected static string $resource = OnboardingChecklistResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
