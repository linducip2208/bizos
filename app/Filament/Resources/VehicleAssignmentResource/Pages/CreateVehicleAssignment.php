<?php

namespace App\Filament\Resources\VehicleAssignmentResource\Pages;

use App\Filament\Resources\VehicleAssignmentResource\VehicleAssignmentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateVehicleAssignment extends CreateRecord
{
    protected static string $resource = VehicleAssignmentResource::class;
    protected function getRedirectUrl(): string { return $this->getResource()::getUrl('index'); }
}
