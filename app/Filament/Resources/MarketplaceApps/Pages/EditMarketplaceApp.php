<?php

namespace App\Filament\Resources\MarketplaceApps\Pages;

use App\Filament\Resources\MarketplaceApps\MarketplaceAppResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMarketplaceApp extends EditRecord
{
    protected static string $resource = MarketplaceAppResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()->label('Hapus'),
        ];
    }
}