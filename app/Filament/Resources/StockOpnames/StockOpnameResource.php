<?php

namespace App\Filament\Resources\StockOpnames;

use App\Filament\Concerns\HasPermissionAccess;
use App\Filament\Resources\StockOpnames\Pages\CreateStockOpname;
use App\Filament\Resources\StockOpnames\Pages\EditStockOpname;
use App\Filament\Resources\StockOpnames\Pages\ListStockOpnames;
use App\Filament\Resources\StockOpnames\RelationManagers\StockOpnameItemsRelationManager;
use App\Filament\Resources\StockOpnames\Schemas\StockOpnameForm;
use App\Filament\Resources\StockOpnames\Tables\StockOpnameTable;
use App\Models\StockOpname;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class StockOpnameResource extends Resource
{
    use HasPermissionAccess;

    protected static ?string $model = StockOpname::class;

    public static function getNavigationGroup(): string|null
    {
        return \App\Filament\Navigation\NavigationGroup::INVENTORY->value;
    }

    protected static ?string $label = 'Stock Opnames';

    protected static ?string $pluralLabel = 'Stock Opname';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArchiveBox;

    protected static ?int $navigationSort = 111;

    protected static ?string $recordTitleAttribute = 'opname_number';

    public static function form(Schema $schema): Schema
    {
        return StockOpnameForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StockOpnameTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            StockOpnameItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStockOpnames::route('/'),
            'create' => CreateStockOpname::route('/create'),
            'edit' => EditStockOpname::route('/{record}/edit'),
        ];
    }
}