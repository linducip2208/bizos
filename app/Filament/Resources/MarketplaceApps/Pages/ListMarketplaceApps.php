<?php

namespace App\Filament\Resources\MarketplaceApps\Pages;

use App\Filament\Resources\MarketplaceApps\MarketplaceAppResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMarketplaceApps extends ListRecords
{
    protected static string $resource = MarketplaceAppResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Tambah Aplikasi'),
        ];
    }
}