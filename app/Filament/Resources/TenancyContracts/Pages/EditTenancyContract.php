<?php

namespace App\Filament\Resources\TenancyContracts\Pages;

use App\Filament\Resources\TenancyContracts\TenancyContractResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTenancyContract extends EditRecord
{
    protected static string $resource = TenancyContractResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}