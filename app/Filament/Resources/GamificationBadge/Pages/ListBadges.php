<?php

namespace App\Filament\Resources\GamificationBadge\Pages;

use App\Filament\Resources\GamificationBadge\GamificationBadgeResource;
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