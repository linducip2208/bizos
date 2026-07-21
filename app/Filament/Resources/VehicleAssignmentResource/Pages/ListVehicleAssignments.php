<?php

namespace App\Filament\Resources\VehicleAssignmentResource\Pages;

use App\Filament\Resources\VehicleAssignmentResource\VehicleAssignmentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListVehicleAssignments extends ListRecords
{
    protected static string $resource = VehicleAssignmentResource::class;
    protected function getHeaderActions(): array { return [CreateAction::make()->label('Tambah Penugasan')]; }
}
