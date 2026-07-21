<?php

namespace App\Filament\Resources\SignatureProviders\Pages;

use App\Filament\Resources\SignatureProviders\SignatureProviderResource;
use Filament\Resources\Pages\EditRecord;

class EditSignatureProvider extends EditRecord
{
    protected static string $resource = SignatureProviderResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (!empty($data['api_key'])) {
            $data['api_key_encrypted'] = encrypt($data['api_key']);
            unset($data['api_key']);
        }
        return $data;
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        if (!empty($data['api_key_encrypted'])) {
            $data['api_key'] = decrypt($data['api_key_encrypted']);
        }
        return $data;
    }
}
