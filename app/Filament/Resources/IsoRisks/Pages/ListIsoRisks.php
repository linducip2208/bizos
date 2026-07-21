<?php

namespace App\Filament\Resources\IsoRisks\Pages;

use App\Filament\Resources\IsoRisks\IsoRiskResource;
use Filament\Resources\Pages\ListRecords;

class ListIsoRisks extends ListRecords
{
    protected static string $resource = IsoRiskResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}