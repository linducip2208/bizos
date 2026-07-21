<?php

namespace App\Filament\Resources\ProductDiscounts;

use App\Filament\Resources\ProductDiscounts\Pages\CreateProductDiscount;
use App\Filament\Resources\ProductDiscounts\Pages\EditProductDiscount;
use App\Filament\Resources\ProductDiscounts\Pages\ListProductDiscounts;
use App\Filament\Resources\ProductDiscounts\Schemas\ProductDiscountForm;
use App\Filament\Resources\ProductDiscounts\Tables\ProductDiscountsTable;
use App\Models\ProductDiscount;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;


use App\Filament\Concerns\HasPermissionAccess;
class ProductDiscountResource extends Resource
{
    use HasPermissionAccess;
    protected static ?string $model = ProductDiscount::class;

    public static function getNavigationGroup(): string|null
    {
        return 'POS';
    }

    protected static ?string $label = 'Diskon Produk';

    protected static ?string $pluralLabel = 'Diskon Produk';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static ?int $navigationSort = 607;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return ProductDiscountForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProductDiscountsTable::configure($table);
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
            'index' => ListProductDiscounts::route('/'),
            'create' => CreateProductDiscount::route('/create'),
            'edit' => EditProductDiscount::route('/{record}/edit'),
        ];
    }
}
