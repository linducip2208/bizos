<?php

namespace App\Filament\Resources\TicketTagResource\Pages;

use App\Filament\Resources\TicketTagResource\TicketTagResource;
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
