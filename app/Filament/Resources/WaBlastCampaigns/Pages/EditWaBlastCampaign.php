<?php

namespace App\Filament\Resources\WaBlastCampaigns\Pages;

use App\Filament\Resources\WaBlastCampaigns\WaBlastCampaignResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditWaBlastCampaign extends EditRecord
{
    protected static string $resource = WaBlastCampaignResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}