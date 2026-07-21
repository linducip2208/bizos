<?php

namespace App\Filament\Resources\TicketResource\Pages;

use App\Filament\Concerns\HasExcelExport;
use App\Filament\Resources\TicketResource\TicketResource;
use App\Models\Ticket;
use App\Services\HelpdeskService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\MaxWidth;

class ListTickets extends ListRecords
{
    use HasExcelExport;

    protected static string $resource = TicketResource::class;

    protected function getCustomHeaderActions(): array
    {
        return [
            CreateAction::make(),
            Action::make('kanban')
                ->label('Lihat Kanban')
                ->icon('heroicon-o-view-columns')
                ->color('gray')
                ->url(fn () => TicketResource::getUrl('kanban')),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Resources\TicketResource\Widgets\TicketQueueStats::class,
        ];
    }
}
