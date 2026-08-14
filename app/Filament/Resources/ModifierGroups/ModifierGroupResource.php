<?php

namespace App\Filament\Resources\ModifierGroups;

use App\Filament\Concerns\HasPermissionAccess;
use App\Filament\Resources\ModifierGroups\Pages\CreateModifierGroup;
use App\Filament\Resources\ModifierGroups\Pages\EditModifierGroup;
use App\Filament\Resources\ModifierGroups\Pages\ListModifierGroups;
use App\Filament\Resources\ModifierGroups\Schemas\ModifierGroupForm;
use App\Filament\Resources\ModifierGroups\Tables\ModifierGroupsTable;
use App\Models\ModifierGroup;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ModifierGroupResource extends Resource
{
    use HasPermissionAccess;

    protected static ?string $model = ModifierGroup::class;

    protected static ?string $label = 'Grup Modifier';

    protected static ?string $pluralLabel = 'Grup Modifier';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPlusCircle;

    protected static ?int $navigationSort = 606;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationGroup(): string|null
    {
        return '📦 Inventory';
    }

    public static function form(Schema $schema): Schema
    {
        return ModifierGroupForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ModifierGroupsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListModifierGroups::route('/'),
            'create' => CreateModifierGroup::route('/create'),
            'edit' => EditModifierGroup::route('/{record}/edit'),
        ];
    }
}
