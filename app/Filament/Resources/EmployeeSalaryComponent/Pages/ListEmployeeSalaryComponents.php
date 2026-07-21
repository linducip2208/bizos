<?php

namespace App\Filament\Resources\EmployeeSalaryComponent\Pages;

use App\Filament\Resources\EmployeeSalaryComponent\EmployeeSalaryComponentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEmployeeSalaryComponents extends ListRecords
{
    protected static string $resource = EmployeeSalaryComponentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}