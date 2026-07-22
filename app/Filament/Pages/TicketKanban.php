<?php

namespace App\Filament\Pages;

use App\Models\Ticket;
use Filament\Pages\Page;

class TicketKanban extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-view-columns';

    protected static ?int $navigationSort = 1102;

    protected static string $view = 'filament.pages.ticket-kanban';

    protected static ?string $title = 'Kanban Tiket';

    protected static ?string $navigationLabel = 'Kanban Tiket';

    protected static ?string $slug = 'ticket-kanban';

    public array $statusColors = [
        'open' => 'border-gray-400',
        'in_progress' => 'border-blue-400',
        'waiting_on_customer' => 'border-amber-400',
        'resolved' => 'border-green-400',
        'closed' => 'border-stone-400',
    ];

    public array $statusLabels = [
        'open' => 'Terbuka',
        'in_progress' => 'Dalam Proses',
        'waiting_on_customer' => 'Menunggu Pelanggan',
        'resolved' => 'Terselesaikan',
        'closed' => 'Tertutup',
    ];

    public array $priorityLabels = [
        'low' => 'Rendah',
        'medium' => 'Sedang',
        'high' => 'Tinggi',
        'urgent' => 'Urgent',
    ];

    public array $priorityBadgeColors = [
        'low' => 'bg-gray-100 text-gray-600',
        'medium' => 'bg-blue-100 text-blue-700',
        'high' => 'bg-orange-100 text-orange-700',
        'urgent' => 'bg-red-100 text-red-700',
    ];

    public static function getNavigationGroup(): ?string
    {
        return '🎫 Support';
    }

    public function mount(): void
    {
    }

    public function getTickets(): array
    {
        $allTickets = Ticket::with(['client', 'assignedTo'])
            ->orderBy('priority')
            ->get();

        $grouped = [];
        foreach (['open', 'in_progress', 'waiting_on_customer', 'resolved', 'closed'] as $status) {
            $grouped[$status] = $allTickets->where('status', $status)->values();
        }

        return $grouped;
    }
}
