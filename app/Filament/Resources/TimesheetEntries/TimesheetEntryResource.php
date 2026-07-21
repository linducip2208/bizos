<?php

namespace App\Filament\Resources\TimesheetEntries;

use App\Filament\Resources\TimesheetEntries\Pages\CreateTimesheetEntry;
use App\Filament\Resources\TimesheetEntries\Pages\EditTimesheetEntry;
use App\Filament\Resources\TimesheetEntries\Pages\ListTimesheetEntries;
use App\Filament\Resources\TimesheetEntries\Schemas\TimesheetEntryForm;
use App\Filament\Resources\TimesheetEntries\Tables\TimesheetEntriesTable;
use App\Models\TimesheetEntry;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;


use App\Filament\Concerns\HasPermissionAccess;
class TimesheetEntryResource extends Resource
{
    use HasPermissionAccess;
    protected static ?string $model = TimesheetEntry::class;

    public static function getNavigationGroup(): string|null
    {
        return '📋 Project Management';
    }

    protected static ?string $label = 'Entri Timesheet';

    protected static ?string $pluralLabel = 'Entri Timesheet';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedListBullet;

    protected static ?int $navigationSort = 510;

    protected static ?string $recordTitleAttribute = 'description';

    public static function form(Schema $schema): Schema
    {
        return TimesheetEntryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TimesheetEntriesTable::configure($table);
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
            'index' => ListTimesheetEntries::route('/'),
            'create' => CreateTimesheetEntry::route('/create'),
            'edit' => EditTimesheetEntry::route('/{record}/edit'),
        ];
    }
}