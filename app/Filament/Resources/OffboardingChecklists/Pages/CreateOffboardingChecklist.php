<?php

namespace App\Filament\Resources\OffboardingChecklists\Pages;

use App\Filament\Resources\OffboardingChecklists\OffboardingChecklistResource;
use Filament\Resources\Pages\CreateRecord;

class CreateOffboardingChecklist extends CreateRecord
{
    protected static string $resource = OffboardingChecklistResource::class;
}