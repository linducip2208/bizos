<?php

namespace App\Filament\Resources\SubcontractOrderResource\Pages;

use App\Filament\Resources\SubcontractOrderResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSubcontractOrder extends EditRecord
{
    protected static string $resource = SubcontractOrderResource::class;

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
