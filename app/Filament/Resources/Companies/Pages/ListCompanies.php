<?php

namespace App\Filament\Resources\Companies\Pages;

use App\Filament\Concerns\HasExcelExport;
use App\Filament\Resources\Companies\CompanyResource;
use Filament\Resources\Pages\ListRecords;

class ListCompanies extends ListRecords
{
    use HasExcelExport;

    protected static string $resource = CompanyResource::class;
}
