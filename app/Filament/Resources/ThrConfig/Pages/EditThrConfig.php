<?php

namespace App\Filament\Resources\ThrConfig\Pages;

use App\Filament\Resources\ThrConfig\ThrConfigResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditThrConfig extends EditRecord
{
    protected static string $resource = ThrConfigResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}