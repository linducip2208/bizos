<?php

namespace App\Filament\Resources\OffboardingChecklists\Pages;

use App\Filament\Resources\OffboardingChecklists\OffboardingChecklistResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditOffboardingChecklist extends EditRecord
{
    protected static string $resource = OffboardingChecklistResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}