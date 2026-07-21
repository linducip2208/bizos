<?php

namespace App\Filament\Resources\SuccessionPlans\Pages;

use App\Filament\Resources\SuccessionPlans\SuccessionPlanResource;
use Filament\Resources\Pages\EditRecord;

class EditSuccessionPlan extends EditRecord
{
    protected static string $resource = SuccessionPlanResource::class;
    protected function getRedirectUrl(): string { return $this->getResource()::getUrl('index'); }
}