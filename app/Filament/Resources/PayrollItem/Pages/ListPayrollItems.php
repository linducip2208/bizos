<?php

namespace App\Filament\Resources\PayrollItem\Pages;

use App\Filament\Resources\PayrollItem\PayrollItemResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPayrollItems extends ListRecords
{
    protected static string $resource = PayrollItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}