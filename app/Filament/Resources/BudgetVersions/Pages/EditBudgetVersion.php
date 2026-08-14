<?php

namespace App\Filament\Resources\BudgetVersions\Pages;

use App\Filament\Resources\BudgetVersions\BudgetVersionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBudgetVersion extends EditRecord
{
    protected static string $resource = BudgetVersionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
