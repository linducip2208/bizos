<?php

namespace App\Filament\Resources\TicketTag\Pages;

use App\Filament\Resources\TicketTag\TicketTagResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTicketTag extends EditRecord
{
    protected static string $resource = TicketTagResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}