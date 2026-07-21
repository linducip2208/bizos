<?php

namespace App\Filament\Resources\VehicleAssignment\Pages;

use App\Filament\Resources\VehicleAssignment\VehicleAssignmentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateVehicleAssignment extends CreateRecord
{
    protected static string $resource = VehicleAssignmentResource::class;
    protected function getRedirectUrl(): string { return $this->getResource()::getUrl('index'); }
}