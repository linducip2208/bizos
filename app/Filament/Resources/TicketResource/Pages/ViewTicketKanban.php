<?php

namespace App\Filament\Resources\TicketResource\Pages;

use App\Filament\Resources\TicketResource\TicketResource;
use App\Models\Ticket;
use Filament\Actions\Action;
use Filament\Resources\Pages\Page;

class ViewTicketKanban extends Page
{
    protected static string $resource = TicketResource::class;

    protected string $view = 'filament.pages.ticket-kanban';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('list_view')
                ->label('Lihat Daftar')
                ->icon('heroicon-o-list-bullet')
                ->color('gray')
                ->url(fn () => TicketResource::getUrl('index')),
        ];
    }

    public function getTickets(): array
    {
        $statuses = ['open', 'in_progress', 'waiting_on_customer', 'resolved', 'closed'];

        $columns = [];

        foreach ($statuses as $status) {
            $columns[$status] = Ticket::where('company_id', auth()->user()?->company_id)
                ->where('status', $status)
                ->with(['assignedTo', 'client', 'category'])
                ->latest('updated_at')
                ->limit(50)
                ->get();
        }

        return $columns;
    }
}
