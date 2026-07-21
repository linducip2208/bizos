<?php

namespace App\Filament\Resources\Employees\Pages;

use App\Filament\Concerns\HasExcelExport;
use App\Filament\Resources\Employees\EmployeeResource;
use Filament\Resources\Pages\ListRecords;

class ListEmployees extends ListRecords
{
    use HasExcelExport;

    protected static string $resource = EmployeeResource::class;
}