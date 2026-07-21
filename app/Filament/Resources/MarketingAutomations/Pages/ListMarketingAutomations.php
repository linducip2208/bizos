<?php

namespace App\Filament\Resources\MarketingAutomations\Pages;

use App\Filament\Concerns\HasExcelExport;
use App\Filament\Resources\MarketingAutomations\MarketingAutomationResource;
use Filament\Resources\Pages\ListRecords;

class ListMarketingAutomations extends ListRecords
{
    use HasExcelExport;

    protected static string $resource = MarketingAutomationResource::class;
}
