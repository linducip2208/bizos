<?php

namespace App\Filament\Resources\CoaCategory\Pages;

use App\Filament\Resources\CoaCategory\CoaCategoryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCoaCategory extends EditRecord
{
    protected static string $resource = CoaCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
