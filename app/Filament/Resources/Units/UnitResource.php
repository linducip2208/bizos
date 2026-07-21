<?php

namespace App\Filament\Resources\Units;

use App\Filament\Resources\Units\Pages;
use App\Models\Unit;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use App\Filament\Concerns\HasPermissionAccess;
use Filament\Support\Icons\Heroicon;

class UnitResource extends Resource
{
    use HasPermissionAccess;

    protected static ?string $model = Unit::class;

    public static function getNavigationGroup(): string|null
    {
        return '📦 Product & Inventory';
    }

    protected static ?string $label = 'Satuan';

    protected static ?string $pluralLabel = 'Satuan';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedScale;

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return Schemas\UnitForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return Tables\UnitTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUnits::route('/'),
            'create' => Pages\CreateUnit::route('/create'),
            'edit' => Pages\EditUnit::route('/{record}/edit'),
        ];
    }
}
