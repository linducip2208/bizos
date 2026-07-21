<?php

namespace App\Filament\Resources\ProgressBillings\Pages;

use App\Filament\Concerns\HasBulkActions;
use App\Filament\Concerns\HasExcelExport;
use App\Filament\Resources\ProgressBillings\ProgressBillingResource;
use Filament\Resources\Pages\ListRecords;

class ListProgressBillings extends ListRecords
{
    use HasBulkActions;
    use HasExcelExport;

    protected static string $resource = ProgressBillingResource::class;
}
