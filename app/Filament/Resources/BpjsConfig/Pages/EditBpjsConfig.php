<?php

namespace App\Filament\Resources\BpjsConfig\Pages;

use App\Filament\Resources\BpjsConfig\BpjsConfigResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBpjsConfig extends EditRecord
{
    protected static string $resource = BpjsConfigResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}