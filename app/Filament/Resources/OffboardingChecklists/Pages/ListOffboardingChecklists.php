<?php

namespace App\Filament\Resources\OffboardingChecklists\Pages;

use App\Filament\Resources\OffboardingChecklists\OffboardingChecklistResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListOffboardingChecklists extends ListRecords
{
    protected static string $resource = OffboardingChecklistResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
