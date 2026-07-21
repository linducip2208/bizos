<?php

namespace App\Filament\Resources\CompetencyResource\Pages;

use App\Filament\Resources\CompetencyResource\CompetencyResource;
use Filament\Resources\Pages\EditRecord;

class EditCompetency extends EditRecord
{
    protected static string $resource = CompetencyResource::class;
    protected function getRedirectUrl(): string { return $this->getResource()::getUrl('index'); }
}
