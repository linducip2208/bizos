<?php

namespace App\Filament\Resources\SignatureProviders\Pages;

use App\Filament\Resources\SignatureProviders\SignatureProviderResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSignatureProvider extends CreateRecord
{
    protected static string $resource = SignatureProviderResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (!empty($data['api_key'])) {
            $data['api_key_encrypted'] = encrypt($data['api_key']);
            unset($data['api_key']);
        }
        return $data;
    }
}