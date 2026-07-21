<?php

namespace App\Filament\Resources\Webhooks\Pages;

use App\Filament\Resources\Webhooks\WebhookResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateWebhook extends CreateRecord
{
    protected static string $resource = WebhookResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if ($data['secret'] ?? null) {
            $data['secret'] = $data['secret'];
        }

        if (isset($data['headers']) && is_string($data['headers'])) {
            $data['headers'] = json_decode($data['headers'], true);
        }

        return $data;
    }
}