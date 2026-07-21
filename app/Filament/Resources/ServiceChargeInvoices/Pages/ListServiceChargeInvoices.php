<?php

namespace App\Filament\Resources\ServiceChargeInvoices\Pages;

use App\Filament\Concerns\HasBulkActions;
use App\Filament\Concerns\HasExcelExport;
use App\Filament\Resources\ServiceChargeInvoices\ServiceChargeInvoiceResource;
use Filament\Resources\Pages\ListRecords;

class ListServiceChargeInvoices extends ListRecords
{
    use HasBulkActions;
    use HasExcelExport;

    protected static string $resource = ServiceChargeInvoiceResource::class;
}
