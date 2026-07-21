<?php

namespace App\Filament\Resources\CalendarEvents;

use App\Filament\Resources\CalendarEvents\Pages\CreateCalendarEvent;
use App\Filament\Resources\CalendarEvents\Pages\EditCalendarEvent;
use App\Filament\Resources\CalendarEvents\Pages\ListCalendarEvents;
use App\Filament\Resources\CalendarEvents\Schemas\CalendarEventForm;
use App\Filament\Resources\CalendarEvents\Tables\CalendarEventsTable;
use App\Models\CalendarEvent;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;


use App\Filament\Concerns\HasPermissionAccess;
class CalendarEventResource extends Resource
{
    use HasPermissionAccess;
    protected static ?string $model = CalendarEvent::class;

    public static function getNavigationGroup(): string|null
    {
        return '?? Project & Work';
    }

    protected static ?string $label = 'Acara Kalender';

    protected static ?string $pluralLabel = 'Acara Kalender';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static ?int $navigationSort = 707;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return CalendarEventForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CalendarEventsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCalendarEvents::route('/'),
            'create' => CreateCalendarEvent::route('/create'),
            'edit' => EditCalendarEvent::route('/{record}/edit'),
        ];
    }
}