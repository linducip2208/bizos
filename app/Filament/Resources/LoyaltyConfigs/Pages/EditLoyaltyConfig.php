<?php

namespace App\Filament\Resources\LoyaltyConfigs\Pages;

use App\Filament\Resources\LoyaltyConfigs\LoyaltyConfigResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditLoyaltyConfig extends EditRecord
{
    protected static string $resource = LoyaltyConfigResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
