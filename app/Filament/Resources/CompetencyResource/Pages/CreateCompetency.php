<?php

namespace App\Filament\Resources\CompetencyResource\Pages;

use App\Filament\Resources\CompetencyResource\CompetencyResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCompetency extends CreateRecord
{
    protected static string $resource = CompetencyResource::class;
    protected function getRedirectUrl(): string { return $this->getResource()::getUrl('index'); }
}
