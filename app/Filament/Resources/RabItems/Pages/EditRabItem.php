<?php

namespace App\Filament\Resources\RabItems\Pages;

use App\Filament\Resources\RabItems\RabItemResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditRabItem extends EditRecord
{
    protected static string $resource = RabItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
