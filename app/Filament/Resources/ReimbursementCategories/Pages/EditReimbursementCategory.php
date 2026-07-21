<?php

namespace App\Filament\Resources\ReimbursementCategories\Pages;

use App\Filament\Resources\ReimbursementCategories\ReimbursementCategoryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditReimbursementCategory extends EditRecord
{
    protected static string $resource = ReimbursementCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
