<?php

namespace App\Filament\Resources\SmsGateway\Pages;

use App\Filament\Resources\SmsGateway\SmsGatewayResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSmsGateway extends CreateRecord
{
    protected static string $resource = SmsGatewayResource::class;
    protected function getRedirectUrl(): string { return $this->getResource()::getUrl('index'); }
}