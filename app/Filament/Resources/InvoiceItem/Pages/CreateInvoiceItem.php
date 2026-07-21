<?php

namespace App\Filament\Resources\InvoiceItem\Pages;

use App\Filament\Resources\InvoiceItem\InvoiceItemResource;
use Filament\Resources\Pages\CreateRecord;

class CreateInvoiceItem extends CreateRecord
{
    protected static string $resource = InvoiceItemResource::class;
}
