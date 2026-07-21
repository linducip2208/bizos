<?php

namespace App\Filament\Resources\SmsGatewayResource\Pages;

use App\Filament\Resources\SmsGatewayResource\SmsGatewayResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSmsGateway extends CreateRecord
{
    protected static string $resource = SmsGatewayResource::class;
    protected function getRedirectUrl(): string { return $this->getResource()::getUrl('index'); }
}
