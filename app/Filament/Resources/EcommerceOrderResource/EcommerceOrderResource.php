<?php

namespace App\Filament\Resources\EcommerceOrderResource;

use App\Filament\Concerns\HasPermissionAccess;
use App\Filament\Resources\EcommerceOrderResource\Pages\CreateEcommerceOrder;
use App\Filament\Resources\EcommerceOrderResource\Pages\EditEcommerceOrder;
use App\Filament\Resources\EcommerceOrderResource\Pages\ListEcommerceOrders;
use App\Filament\Resources\EcommerceOrderResource\Schemas\EcommerceOrderForm;
use App\Filament\Resources\EcommerceOrderResource\Tables\EcommerceOrderTable;
use App\Models\EcommerceOrder;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class EcommerceOrderResource extends Resource
{
    use HasPermissionAccess;

    protected static ?string $model = EcommerceOrder::class;

    public static function getNavigationGroup(): string|null
    {
        return 'E-Commerce';
    }

    protected static ?string $label = 'Pesanan';

    protected static ?string $pluralLabel = 'Pesanan E-Commerce';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingBag;

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'channel_order_id';

    public static function form(Schema $schema): Schema
    {
        return EcommerceOrderForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EcommerceOrderTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEcommerceOrders::route('/'),
            'create' => CreateEcommerceOrder::route('/create'),
            'edit' => EditEcommerceOrder::route('/{record}/edit'),
        ];
    }
}
