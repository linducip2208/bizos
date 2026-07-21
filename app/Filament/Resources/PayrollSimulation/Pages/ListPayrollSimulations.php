<?php

namespace App\Filament\Resources\PayrollSimulation\Pages;

use App\Filament\Resources\PayrollSimulation\PayrollSimulationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPayrollSimulations extends ListRecords
{
    protected static string $resource = PayrollSimulationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
