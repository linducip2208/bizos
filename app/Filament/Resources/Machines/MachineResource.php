<?php

namespace App\Filament\Resources\Machines;

use App\Filament\Resources\Machines\Pages;
use App\Models\Machine;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use App\Filament\Concerns\HasPermissionAccess;
use Filament\Support\Icons\Heroicon;

class MachineResource extends Resource
{
    use HasPermissionAccess;

    protected static ?string $model = Machine::class;

    public static function getNavigationGroup(): string|null
    {
        return '🏭 Manufaktur';
    }

    protected static ?string $label = 'Mesin';

    protected static ?string $pluralLabel = 'Mesin';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedWrenchScrewdriver;

    protected static ?int $navigationSort = 6;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return Schemas\MachineForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return Tables\MachineTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMachines::route('/'),
            'create' => Pages\CreateMachine::route('/create'),
            'edit' => Pages\EditMachine::route('/{record}/edit'),
        ];
    }
}
