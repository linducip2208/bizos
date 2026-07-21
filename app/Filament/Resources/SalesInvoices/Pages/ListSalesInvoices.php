<?php

namespace App\Filament\Resources\SalesInvoices\Pages;

use App\Filament\Concerns\HasExcelExport;
use App\Filament\Resources\SalesInvoices\SalesInvoiceResource;
use Filament\Resources\Pages\ListRecords;

class ListSalesInvoices extends ListRecords
{
    use HasExcelExport;

    protected static string $resource = SalesInvoiceResource::class;
}
