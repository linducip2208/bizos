<?php

namespace App\Filament\Resources\GamificationBadge\Pages;

use App\Filament\Resources\GamificationBadge\GamificationBadgeResource;
use Filament\Resources\Pages\EditRecord;

class EditBadge extends EditRecord
{
    protected static string $resource = GamificationBadgeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\DeleteAction::make(),
        ];
    }
}