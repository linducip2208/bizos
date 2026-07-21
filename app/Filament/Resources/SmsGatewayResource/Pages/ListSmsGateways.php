<?php

namespace App\Filament\Resources\SmsGatewayResource\Pages;

use App\Filament\Resources\SmsGatewayResource\SmsGatewayResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSmsGateways extends ListRecords
{
    protected static string $resource = SmsGatewayResource::class;
    protected function getHeaderActions(): array { return [CreateAction::make()->label('Tambah Gateway')]; }
}
