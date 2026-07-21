<?php

namespace App\Filament\Resources\LabResults\Pages;

use App\Filament\Resources\LabResults\LabResultResource;
use App\Services\HealthcareService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateLabResult extends CreateRecord
{
    protected static string $resource = LabResultResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return app(HealthcareService::class)->recordLabResult($data);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
