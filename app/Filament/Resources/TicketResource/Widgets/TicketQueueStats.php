<?php

namespace App\Filament\Resources\TicketResource\Widgets;

use App\Models\Ticket;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TicketQueueStats extends BaseWidget
{
    protected function getStats(): array
    {
        $companyId = auth()->user()?->company_id;

        $open = Ticket::where('company_id', $companyId)->where('status', 'open')->count();
        $inProgress = Ticket::where('company_id', $companyId)->where('status', 'in_progress')->count();
        $waiting = Ticket::where('company_id', $companyId)->where('status', 'waiting_on_customer')->count();
        $overdue = Ticket::where('company_id', $companyId)
            ->whereNotIn('status', ['resolved', 'closed'])
            ->whereNotNull('due_date')
            ->where('due_date', '<', now())
            ->count();
        $unassigned = Ticket::where('company_id', $companyId)
            ->whereNull('assigned_to')
            ->whereNotIn('status', ['resolved', 'closed'])
            ->count();

        return [
            Stat::make('Terbuka', $open)
                ->color('info')
                ->icon('heroicon-o-ticket'),
            Stat::make('Dalam Proses', $inProgress)
                ->color('warning')
                ->icon('heroicon-o-arrow-path'),
            Stat::make('Menunggu', $waiting)
                ->color('gray')
                ->icon('heroicon-o-clock'),
            Stat::make('Terlambat', $overdue)
                ->color('danger')
                ->icon('heroicon-o-exclamation-triangle'),
            Stat::make('Belum Di-assign', $unassigned)
                ->color('gray')
                ->icon('heroicon-o-user-minus'),
        ];
    }
}
