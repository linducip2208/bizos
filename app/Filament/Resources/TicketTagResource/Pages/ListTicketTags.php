<?php

namespace App\Filament\Resources\TicketTagResource\Pages;

use App\Filament\Resources\TicketTagResource\TicketTagResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTicketTags extends ListRecords
{
    protected static string $resource = TicketTagResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
