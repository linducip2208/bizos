<?php

namespace App\Filament\Resources\BudgetItem\Pages;

use App\Filament\Resources\BudgetItem\BudgetItemResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBudgetItems extends ListRecords
{
    protected static string $resource = BudgetItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
