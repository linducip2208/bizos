<?php

namespace App\Filament\Resources\GamificationBadgeResource\Pages;

use App\Filament\Resources\GamificationBadgeResource\GamificationBadgeResource;
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
