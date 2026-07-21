<?php

namespace App\Filament\Resources\WorkCalendars;

use App\Filament\Concerns\HasPermissionAccess;
use App\Filament\Resources\WorkCalendars\Pages\CreateWorkCalendar;
use App\Filament\Resources\WorkCalendars\Pages\EditWorkCalendar;
use App\Filament\Resources\WorkCalendars\Pages\ListWorkCalendars;
use App\Filament\Resources\WorkCalendars\Schemas\WorkCalendarForm;
use App\Filament\Resources\WorkCalendars\Tables\WorkCalendarsTable;
use App\Models\WorkCalendar;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class WorkCalendarResource extends Resource
{
    use HasPermissionAccess;

    protected static ?string $model = WorkCalendar::class;

    public static function getNavigationGroup(): string|null
    {
        return '🏢 Organisasi';
    }

    protected static ?string $label = 'Kalender Kerja';

    protected static ?string $pluralLabel = 'Kalender Kerja';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendar;

    protected static ?int $navigationSort = 9;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return WorkCalendarForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WorkCalendarsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWorkCalendars::route('/'),
            'create' => CreateWorkCalendar::route('/create'),
            'edit' => EditWorkCalendar::route('/{record}/edit'),
        ];
    }
}
