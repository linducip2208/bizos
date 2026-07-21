<?php

namespace App\Filament\Resources\TenancyContracts\Pages;

use App\Filament\Resources\TenancyContracts\TenancyContractResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTenancyContract extends CreateRecord
{
    protected static string $resource = TenancyContractResource::class;
}