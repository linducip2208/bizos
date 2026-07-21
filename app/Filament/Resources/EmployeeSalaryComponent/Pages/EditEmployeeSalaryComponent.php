<?php

namespace App\Filament\Resources\EmployeeSalaryComponent\Pages;

use App\Filament\Resources\EmployeeSalaryComponent\EmployeeSalaryComponentResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditEmployeeSalaryComponent extends EditRecord
{
    protected static string $resource = EmployeeSalaryComponentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}