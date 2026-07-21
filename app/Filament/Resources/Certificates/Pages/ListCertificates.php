<?php

namespace App\Filament\Resources\Certificates\Pages;

use App\Filament\Concerns\HasBulkActions;
use App\Filament\Concerns\HasExcelExport;
use App\Filament\Resources\Certificates\CertificateResource;
use Filament\Resources\Pages\ListRecords;

class ListCertificates extends ListRecords
{
    use HasExcelExport, HasBulkActions;

    protected static string $resource = CertificateResource::class;

    protected function getCustomHeaderActions(): array
    {
        return [];
    }
}
