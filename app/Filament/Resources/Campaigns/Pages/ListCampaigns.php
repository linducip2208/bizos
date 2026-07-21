<?php

namespace App\Filament\Resources\Campaigns\Pages;

use App\Filament\Concerns\HasExcelExport;
use App\Filament\Resources\Campaigns\CampaignResource;
use Filament\Resources\Pages\ListRecords;

class ListCampaigns extends ListRecords
{
    use HasExcelExport;

    protected static string $resource = CampaignResource::class;
}
