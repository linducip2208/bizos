<?php

namespace App\Filament\Resources\IsoAudits\Pages;

use App\Filament\Resources\IsoAudits\IsoAuditResource;
use Filament\Resources\Pages\EditRecord;

class EditIsoAudit extends EditRecord
{
    protected static string $resource = IsoAuditResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}