<?php

namespace App\Filament\Resources\BudgetItem\Pages;

use App\Filament\Resources\BudgetItem\BudgetItemResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBudgetItem extends EditRecord
{
    protected static string $resource = BudgetItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}