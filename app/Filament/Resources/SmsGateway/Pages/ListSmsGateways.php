<?php

namespace App\Filament\Resources\SmsGateway\Pages;

use App\Filament\Resources\SmsGateway\SmsGatewayResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSmsGateways extends ListRecords
{
    protected static string $resource = SmsGatewayResource::class;
    protected function getHeaderActions(): array { return [CreateAction::make()->label('Tambah Gateway')]; }
}