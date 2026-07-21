<?php

namespace App\Filament\Resources\Invoices\Pages;

use App\Filament\Concerns\HasExcelExport;
use App\Filament\Resources\Invoices\InvoiceResource;
use Filament\Resources\Pages\ListRecords;

class ListInvoices extends ListRecords
{
    use HasExcelExport;

    protected static string $resource = InvoiceResource::class;
}