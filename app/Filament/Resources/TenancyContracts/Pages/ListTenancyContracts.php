<?php

namespace App\Filament\Resources\TenancyContracts\Pages;

use App\Filament\Concerns\HasBulkActions;
use App\Filament\Concerns\HasExcelExport;
use App\Filament\Resources\TenancyContracts\TenancyContractResource;
use Filament\Resources\Pages\ListRecords;

class ListTenancyContracts extends ListRecords
{
    use HasBulkActions;
    use HasExcelExport;

    protected static string $resource = TenancyContractResource::class;
}