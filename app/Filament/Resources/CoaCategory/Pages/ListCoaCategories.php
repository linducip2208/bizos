<?php

namespace App\Filament\Resources\CoaCategory\Pages;

use App\Filament\Resources\CoaCategory\CoaCategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCoaCategories extends ListRecords
{
    protected static string $resource = CoaCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
