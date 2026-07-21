<?php

namespace App\Filament\Resources\SubcontractorContracts\Pages;

use App\Filament\Concerns\HasBulkActions;
use App\Filament\Concerns\HasExcelExport;
use App\Filament\Resources\SubcontractorContracts\SubcontractorContractResource;
use Filament\Resources\Pages\ListRecords;

class ListSubcontractorContracts extends ListRecords
{
    use HasBulkActions;
    use HasExcelExport;

    protected static string $resource = SubcontractorContractResource::class;
}