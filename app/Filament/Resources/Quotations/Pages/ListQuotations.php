<?php

namespace App\Filament\Resources\Quotations\Pages;

use App\Filament\Concerns\HasExcelExport;
use App\Filament\Resources\Quotations\QuotationResource;
use Filament\Resources\Pages\ListRecords;

class ListQuotations extends ListRecords
{
    use HasExcelExport;

    protected static string $resource = QuotationResource::class;
}
