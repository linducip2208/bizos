<?php

namespace App\Filament\Resources\Shifts;

use App\Filament\Resources\Shifts\Pages\CreateShift;
use App\Filament\Resources\Shifts\Pages\EditShift;
use App\Filament\Resources\Shifts\Pages\ListShifts;
use App\Filament\Resources\Shifts\Schemas\ShiftForm;
use App\Filament\Resources\Shifts\Tables\ShiftsTable;
use App\Models\Shift;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;


use App\Filament\Concerns\HasPermissionAccess;
class ShiftResource extends Resource
{
    use HasPermissionAccess;
    protected static ?string $model = Shift::class;

    public static function getNavigationGroup(): string|null
    {
        return \App\Filament\Navigation\NavigationGroup::HUMAN_CAPITAL->value;
    }

    protected static ?string $label = 'Shifts';

    protected static ?string $pluralLabel = 'Shifts';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArchiveBox;

    protected static ?int $navigationSort = 101;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return ShiftForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ShiftsTable::configure($table);
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
            'index' => ListShifts::route('/'),
            'create' => CreateShift::route('/create'),
            'edit' => EditShift::route('/{record}/edit'),
        ];
    }
}