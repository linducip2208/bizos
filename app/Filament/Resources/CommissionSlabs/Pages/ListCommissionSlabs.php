<?php

namespace App\Filament\Resources\CommissionSlabs\Pages;

use App\Filament\Concerns\HasBulkActions;
use App\Filament\Concerns\HasExcelExport;
use App\Filament\Resources\CommissionSlabs\CommissionSlabResource;
use Filament\Resources\Pages\ListRecords;

class ListCommissionSlabs extends ListRecords
{
    use HasExcelExport, HasBulkActions;

    protected static string $resource = CommissionSlabResource::class;
}
