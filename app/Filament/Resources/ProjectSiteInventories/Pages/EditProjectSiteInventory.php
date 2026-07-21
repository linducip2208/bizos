<?php

namespace App\Filament\Resources\ProjectSiteInventories\Pages;

use App\Filament\Resources\ProjectSiteInventories\ProjectSiteInventoryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditProjectSiteInventory extends EditRecord
{
    protected static string $resource = ProjectSiteInventoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
