<?php

namespace App\Filament\Resources\MarketingAutomations\Pages;

use App\Filament\Resources\MarketingAutomations\MarketingAutomationResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMarketingAutomation extends EditRecord
{
    protected static string $resource = MarketingAutomationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
