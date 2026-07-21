<?php

namespace App\Filament\Resources\Pph21Config\Pages;

use App\Filament\Resources\Pph21Config\Pph21ConfigResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPph21Config extends EditRecord
{
    protected static string $resource = Pph21ConfigResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
