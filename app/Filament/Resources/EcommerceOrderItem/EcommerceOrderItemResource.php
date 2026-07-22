<?php

namespace App\Filament\Resources\EcommerceOrderItem;

use App\Filament\Concerns\HasPermissionAccess;
use App\Filament\Resources\EcommerceOrderItem\Pages\CreateEcommerceOrderItem;
use App\Filament\Resources\EcommerceOrderItem\Pages\EditEcommerceOrderItem;
use App\Filament\Resources\EcommerceOrderItem\Pages\ListEcommerceOrderItems;
use App\Filament\Resources\EcommerceOrderItem\Schemas\EcommerceOrderItemForm;
use App\Filament\Resources\EcommerceOrderItem\Tables\EcommerceOrderItemTable;
use App\Models\EcommerceOrderItem;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class EcommerceOrderItemResource extends Resource
{
    protected static bool $shouldRegisterNavigation = false;
{
    use HasPermissionAccess;

    protected static ?string $model = EcommerceOrderItem::class;

    public static function getNavigationGroup(): string|null
    {
        return '🏭 Industry';
    }

    protected static ?string $label = 'Item Pesanan';

    protected static ?string $pluralLabel = 'Item Pesanan';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedListBullet;

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'product_name';

    public static function form(Schema $schema): Schema
    {
        return EcommerceOrderItemForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EcommerceOrderItemTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEcommerceOrderItems::route('/'),
            'create' => CreateEcommerceOrderItem::route('/create'),
            'edit' => EditEcommerceOrderItem::route('/{record}/edit'),
        ];
    }
}