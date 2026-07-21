<?php

namespace App\Filament\Resources\IntegrationConnectors\Pages;

use App\Filament\Resources\IntegrationConnectors\IntegrationConnectorResource;
use Filament\Resources\Pages\CreateRecord;

class CreateIntegrationConnector extends CreateRecord
{
    protected static string $resource = IntegrationConnectorResource::class;
}