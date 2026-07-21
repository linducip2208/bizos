<?php

namespace App\Filament\Resources\Leads\Pages;

use App\Filament\Concerns\HasExcelExport;
use App\Filament\Resources\Leads\LeadResource;
use Filament\Resources\Pages\ListRecords;

class ListLeads extends ListRecords
{
    use HasExcelExport;

    protected static string $resource = LeadResource::class;
}
