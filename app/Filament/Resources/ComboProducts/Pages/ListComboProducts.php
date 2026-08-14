<?php

namespace App\Filament\Resources\ComboProducts\Pages;

use App\Filament\Resources\ComboProducts\ComboProductResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListComboProducts extends ListRecords
{
    protected static string $resource = ComboProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
