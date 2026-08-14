<?php

namespace App\Filament\Resources\ComboProducts\Pages;

use App\Filament\Resources\ComboProducts\ComboProductResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditComboProduct extends EditRecord
{
    protected static string $resource = ComboProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
