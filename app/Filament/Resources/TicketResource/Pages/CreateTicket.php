<?php

namespace App\Filament\Resources\TicketResource\Pages;

use App\Filament\Resources\TicketResource\TicketResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTicket extends CreateRecord
{
    protected static string $resource = TicketResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['company_id'] = auth()->user()?->company_id;
        $data['ticket_number'] = (new \App\Services\HelpdeskService)->generateTicketNumber();

        return $data;
    }

    protected function afterCreate(): void
    {
        $ticket = $this->record;

        app(\App\Services\HelpdeskService::class)->applySlaPolicy($ticket);

        $ticket->activities()->create([
            'employee_id' => auth()->user()?->employee_id,
            'activity_type' => 'created',
            'new_value' => $ticket->status,
            'created_at' => now(),
        ]);
    }
}
