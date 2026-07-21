<?php

namespace App\Filament\Resources\SubcontractorContracts\Pages;

use App\Filament\Resources\SubcontractorContracts\SubcontractorContractResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSubcontractorContract extends EditRecord
{
    protected static string $resource = SubcontractorContractResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}