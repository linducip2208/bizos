<?php

namespace App\Filament\Resources\PayrollApproval\Pages;

use App\Filament\Resources\PayrollApproval\PayrollApprovalResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPayrollApprovals extends ListRecords
{
    protected static string $resource = PayrollApprovalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
