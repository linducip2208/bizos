<?php

namespace App\Filament\Resources\IsoAudits\Pages;

use App\Filament\Resources\IsoAudits\IsoAuditResource;
use Filament\Resources\Pages\ListRecords;

class ListIsoAudits extends ListRecords
{
    protected static string $resource = IsoAuditResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
