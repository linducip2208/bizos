<?php

namespace App\Filament\Resources\RecurringInvoices\Pages;

use App\Filament\Concerns\HasExcelExport;
use App\Filament\Resources\RecurringInvoices\RecurringInvoiceResource;
use Filament\Resources\Pages\ListRecords;

class ListRecurringInvoices extends ListRecords
{
    use HasExcelExport;

    protected static string $resource = RecurringInvoiceResource::class;
}
