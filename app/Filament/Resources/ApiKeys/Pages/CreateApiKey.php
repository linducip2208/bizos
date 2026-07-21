<?php

namespace App\Filament\Resources\ApiKeys\Pages;

use App\Filament\Resources\ApiKeys\ApiKeyResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateApiKey extends CreateRecord
{
    protected static string $resource = ApiKeyResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $token = Str::random(64);
        $data['key'] = hash('sha256', $token);

        session()->flash('api_key_token', $token);

        return $data;
    }

    protected function afterCreate(): void
    {
        $token = session('api_key_token');
        if ($token) {
            \Filament\Notifications\Notification::make()
                ->title('Kunci API Berhasil Dibuat')
                ->body("Salin kunci API ini sekarang. Tidak akan ditampilkan lagi:\n\n{$token}")
                ->warning()
                ->persistent()
                ->send();

            session()->forget('api_key_token');
        }
    }
}
