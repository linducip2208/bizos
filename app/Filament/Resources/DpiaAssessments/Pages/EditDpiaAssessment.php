<?php

namespace App\Filament\Resources\DpiaAssessments\Pages;

use App\Filament\Resources\DpiaAssessments\DpiaAssessmentResource;
use Filament\Resources\Pages\EditRecord;

class EditDpiaAssessment extends EditRecord
{
    protected static string $resource = DpiaAssessmentResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}