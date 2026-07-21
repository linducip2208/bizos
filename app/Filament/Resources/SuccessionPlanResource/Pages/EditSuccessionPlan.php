<?php

namespace App\Filament\Resources\SuccessionPlanResource\Pages;

use App\Filament\Resources\SuccessionPlanResource\SuccessionPlanResource;
use Filament\Resources\Pages\EditRecord;

class EditSuccessionPlan extends EditRecord
{
    protected static string $resource = SuccessionPlanResource::class;
    protected function getRedirectUrl(): string { return $this->getResource()::getUrl('index'); }
}
