<?php

namespace App\Filament\Resources\CommissionSlabs\Pages;

use App\Filament\Resources\CommissionSlabs\CommissionSlabResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCommissionSlab extends EditRecord
{
    protected static string $resource = CommissionSlabResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}