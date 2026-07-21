<?php

namespace App\Filament\Resources\PayrollItem\Pages;

use App\Filament\Resources\PayrollItem\PayrollItemResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPayrollItem extends EditRecord
{
    protected static string $resource = PayrollItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}