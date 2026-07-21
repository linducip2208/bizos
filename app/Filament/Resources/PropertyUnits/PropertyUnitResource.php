<?php

namespace App\Filament\Resources\PropertyUnits;

use App\Filament\Concerns\HasPermissionAccess;
use App\Filament\Resources\PropertyUnits\Pages\CreatePropertyUnit;
use App\Filament\Resources\PropertyUnits\Pages\EditPropertyUnit;
use App\Filament\Resources\PropertyUnits\Pages\ListPropertyUnits;
use App\Filament\Resources\PropertyUnits\Schemas\PropertyUnitForm;
use App\Filament\Resources\PropertyUnits\Tables\PropertyUnitTable;
use App\Models\PropertyUnit;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PropertyUnitResource extends Resource
{
    use HasPermissionAccess;

    protected static ?string $model = PropertyUnit::class;

    public static function getNavigationGroup(): string|null
    {
        return '🏠 Properti';
    }

    protected static ?string $label = 'Unit Properti';
    protected static ?string $pluralLabel = 'Unit Properti';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice;

    protected static ?int $navigationSort = 801;

    protected static ?string $recordTitleAttribute = 'unit_number';

    public static function form(Schema $schema): Schema
    {
        return PropertyUnitForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PropertyUnitTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPropertyUnits::route('/'),
            'create' => CreatePropertyUnit::route('/create'),
            'edit' => EditPropertyUnit::route('/{record}/edit'),
        ];
    }
}
