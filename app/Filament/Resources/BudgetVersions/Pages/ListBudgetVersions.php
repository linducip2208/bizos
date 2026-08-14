<?php

namespace App\Filament\Resources\BudgetVersions\Pages;

use App\Filament\Resources\BudgetVersions\BudgetVersionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListBudgetVersions extends ListRecords
{
    protected static string $resource = BudgetVersionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
