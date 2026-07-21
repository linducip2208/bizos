<?php

namespace App\Filament\Resources\BomItemResource\Pages;

use App\Filament\Resources\BomItemResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBomItem extends EditRecord
{
    protected static string $resource = BomItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
