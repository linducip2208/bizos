<?php

namespace App\Filament\Resources\Payroll\Pages;

use App\Filament\Concerns\HasExcelExport;
use App\Filament\Resources\Payroll\PayrollResource;
use Filament\Resources\Pages\ListRecords;

class ListPayrolls extends ListRecords
{
    use HasExcelExport;

    protected static string $resource = PayrollResource::class;
}
