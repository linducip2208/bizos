<?php

namespace App\Filament\Resources\StockOpnameItems;

use App\Filament\Concerns\HasPermissionAccess;
use App\Filament\Resources\StockOpnameItems\Pages\CreateStockOpnameItem;
use App\Filament\Resources\StockOpnameItems\Pages\EditStockOpnameItem;
use App\Filament\Resources\StockOpnameItems\Pages\ListStockOpnameItems;
use App\Filament\Resources\StockOpnameItems\Schemas\StockOpnameItemForm;
use App\Filament\Resources\StockOpnameItems\Tables\StockOpnameItemTable;
use App\Models\StockOpnameItem;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class StockOpnameItemResource extends Resource
{
    protected static bool $shouldRegisterNavigation = false;
{
    use HasPermissionAccess;

    protected static ?string $model = StockOpnameItem::class;

    public static function getNavigationGroup(): string|null
    {
        return '📦 Product & Inventory';
    }

    protected static ?string $label = 'Item Opname';

    protected static ?string $pluralLabel = 'Item Opname';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentMagnifyingGlass;

    protected static ?int $navigationSort = 112;

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return StockOpnameItemForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StockOpnameItemTable::configure($table);
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
            'index' => ListStockOpnameItems::route('/'),
            'create' => CreateStockOpnameItem::route('/create'),
            'edit' => EditStockOpnameItem::route('/{record}/edit'),
        ];
    }
}