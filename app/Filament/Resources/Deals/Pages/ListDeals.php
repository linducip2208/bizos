<?php

namespace App\Filament\Resources\Deals\Pages;

use App\Filament\Concerns\HasExcelExport;
use App\Filament\Resources\Deals\DealResource;
use Filament\Resources\Pages\ListRecords;

class ListDeals extends ListRecords
{
    use HasExcelExport;

    protected static string $resource = DealResource::class;
}