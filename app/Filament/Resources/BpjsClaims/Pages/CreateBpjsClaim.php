<?php

namespace App\Filament\Resources\BpjsClaims\Pages;

use App\Filament\Resources\BpjsClaims\BpjsClaimResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBpjsClaim extends CreateRecord
{
    protected static string $resource = BpjsClaimResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}