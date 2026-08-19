<?php

namespace App\Filament\Resources\Timesheets;

use App\Filament\Resources\Timesheets\Pages\CreateTimesheet;
use App\Filament\Resources\Timesheets\Pages\EditTimesheet;
use App\Filament\Resources\Timesheets\Pages\ListTimesheets;
use App\Filament\Resources\Timesheets\Schemas\TimesheetForm;
use App\Filament\Resources\Timesheets\Tables\TimesheetTable;
use App\Models\Timesheet;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;


use App\Filament\Concerns\HasPermissionAccess;
class TimesheetResource extends Resource
{
    use HasPermissionAccess;
    protected static ?string $model = Timesheet::class;

    public static function getNavigationGroup(): string|null
    {
        return \App\Filament\Navigation\NavigationGroup::PROJECTS->value;
    }

    protected static ?string $label = 'Timesheets';

    protected static ?string $pluralLabel = 'Timesheets';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArchiveBox;

    protected static ?int $navigationSort = 505;

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return TimesheetForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TimesheetTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTimesheets::route('/'),
            'create' => CreateTimesheet::route('/create'),
            'edit' => EditTimesheet::route('/{record}/edit'),
        ];
    }
}