<?php

namespace App\Filament\Resources\ComboProducts;

use App\Filament\Concerns\HasPermissionAccess;
use App\Filament\Resources\ComboProducts\Pages\CreateComboProduct;
use App\Filament\Resources\ComboProducts\Pages\EditComboProduct;
use App\Filament\Resources\ComboProducts\Pages\ListComboProducts;
use App\Filament\Resources\ComboProducts\Schemas\ComboProductForm;
use App\Filament\Resources\ComboProducts\Tables\ComboProductsTable;
use App\Models\ComboProduct;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ComboProductResource extends Resource
{
    use HasPermissionAccess;

    protected static ?string $model = ComboProduct::class;

    protected static ?string $label = 'Produk Kombo';

    protected static ?string $pluralLabel = 'Produk Kombo';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquaresPlus;

    protected static ?int $navigationSort = 607;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationGroup(): string|null
    {
        return '📦 Inventory';
    }

    public static function form(Schema $schema): Schema
    {
        return ComboProductForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ComboProductsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListComboProducts::route('/'),
            'create' => CreateComboProduct::route('/create'),
            'edit' => EditComboProduct::route('/{record}/edit'),
        ];
    }
}
