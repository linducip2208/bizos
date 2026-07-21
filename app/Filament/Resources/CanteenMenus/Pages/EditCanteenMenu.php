<?php

namespace App\Filament\Resources\CanteenMenus\Pages;

use App\Filament\Resources\CanteenMenus\CanteenMenuResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCanteenMenu extends EditRecord
{
    protected static string $resource = CanteenMenuResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
