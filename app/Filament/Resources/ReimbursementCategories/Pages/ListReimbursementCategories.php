<?php

namespace App\Filament\Resources\ReimbursementCategories\Pages;

use App\Filament\Resources\ReimbursementCategories\ReimbursementCategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListReimbursementCategories extends ListRecords
{
    protected static string $resource = ReimbursementCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}