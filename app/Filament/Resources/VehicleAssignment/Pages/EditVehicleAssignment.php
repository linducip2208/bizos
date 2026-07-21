<?php

namespace App\Filament\Resources\VehicleAssignment\Pages;

use App\Filament\Resources\VehicleAssignment\VehicleAssignmentResource;
use Filament\Resources\Pages\EditRecord;

class EditVehicleAssignment extends EditRecord
{
    protected static string $resource = VehicleAssignmentResource::class;
    protected function getRedirectUrl(): string { return $this->getResource()::getUrl('index'); }
}