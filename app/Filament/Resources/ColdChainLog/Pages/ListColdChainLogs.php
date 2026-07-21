<?php

namespace App\Filament\Resources\ColdChainLog\Pages;

use App\Filament\Resources\ColdChainLog\ColdChainLogResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListColdChainLogs extends ListRecords
{
    protected static string $resource = ColdChainLogResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('Tambah Log')];
    }
}