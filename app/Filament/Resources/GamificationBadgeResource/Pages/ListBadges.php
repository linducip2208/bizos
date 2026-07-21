<?php

namespace App\Filament\Resources\GamificationBadgeResource\Pages;

use App\Filament\Resources\GamificationBadgeResource\GamificationBadgeResource;
use Filament\Resources\Pages\ListRecords;

class ListBadges extends ListRecords
{
    protected static string $resource = GamificationBadgeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make(),
        ];
    }
}
