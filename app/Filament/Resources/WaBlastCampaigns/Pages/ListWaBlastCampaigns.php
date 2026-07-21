<?php

namespace App\Filament\Resources\WaBlastCampaigns\Pages;

use App\Filament\Resources\WaBlastCampaigns\WaBlastCampaignResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWaBlastCampaigns extends ListRecords
{
    protected static string $resource = WaBlastCampaignResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}