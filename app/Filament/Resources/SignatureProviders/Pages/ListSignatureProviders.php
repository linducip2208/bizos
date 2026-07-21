<?php

namespace App\Filament\Resources\SignatureProviders\Pages;

use App\Filament\Resources\SignatureProviders\SignatureProviderResource;
use Filament\Resources\Pages\ListRecords;

class ListSignatureProviders extends ListRecords
{
    protected static string $resource = SignatureProviderResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}