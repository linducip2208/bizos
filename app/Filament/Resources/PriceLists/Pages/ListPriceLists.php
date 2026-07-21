<?php

namespace App\Filament\Resources\PriceLists\Pages;

use App\Filament\Concerns\HasExcelExport;
use App\Filament\Resources\PriceLists\PriceListResource;
use Filament\Resources\Pages\ListRecords;

class ListPriceLists extends ListRecords
{
    use HasExcelExport;

    protected static string $resource = PriceListResource::class;
}
