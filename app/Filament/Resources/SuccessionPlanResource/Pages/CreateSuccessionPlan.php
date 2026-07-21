<?php

namespace App\Filament\Resources\SuccessionPlanResource\Pages;

use App\Filament\Resources\SuccessionPlanResource\SuccessionPlanResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSuccessionPlan extends CreateRecord
{
    protected static string $resource = SuccessionPlanResource::class;
    protected function getRedirectUrl(): string { return $this->getResource()::getUrl('index'); }
}
