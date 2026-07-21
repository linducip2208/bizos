<?php

namespace App\Filament\Resources\Ticket;

use App\Filament\Concerns\HasPermissionAccess;
use App\Filament\Resources\Ticket\Pages\CreateTicket;
use App\Filament\Resources\Ticket\Pages\EditTicket;
use App\Filament\Resources\Ticket\Pages\ListTickets;
use App\Filament\Resources\Ticket\Pages\ViewTicketKanban;
use App\Filament\Resources\Ticket\Schemas\TicketForm;
use App\Filament\Resources\Ticket\Tables\TicketsTable;
use App\Models\Ticket;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TicketResource extends Resource
{
    use HasPermissionAccess;

    protected static ?string $model = Ticket::class;

    public static function getNavigationGroup(): string|null
    {
        return 'Helpdesk';
    }

    protected static ?string $label = 'Tiket';

    protected static ?string $pluralLabel = 'Tiket';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTicket;

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'subject';

    public static function form(Schema $schema): Schema
    {
        return TicketForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TicketsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTickets::route('/'),
            'kanban' => ViewTicketKanban::route('/kanban'),
            'create' => CreateTicket::route('/create'),
            'edit' => EditTicket::route('/{record}/edit'),
        ];
    }
}