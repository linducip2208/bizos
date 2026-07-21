<?php

namespace App\Filament\Resources\SmsGateway\Pages;

use App\Filament\Resources\SmsGateway\SmsGatewayResource;
use Filament\Resources\Pages\EditRecord;

class EditSmsGateway extends EditRecord
{
    protected static string $resource = SmsGatewayResource::class;
    protected function getRedirectUrl(): string { return $this->getResource()::getUrl('index'); }
}