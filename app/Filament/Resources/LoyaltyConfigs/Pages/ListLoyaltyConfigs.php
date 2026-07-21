<?php

namespace App\Filament\Resources\LoyaltyConfigs\Pages;

use App\Filament\Concerns\HasBulkActions;
use App\Filament\Concerns\HasExcelExport;
use App\Filament\Resources\LoyaltyConfigs\LoyaltyConfigResource;
use Filament\Resources\Pages\ListRecords;

class ListLoyaltyConfigs extends ListRecords
{
    use HasExcelExport, HasBulkActions;

    protected static string $resource = LoyaltyConfigResource::class;
}
