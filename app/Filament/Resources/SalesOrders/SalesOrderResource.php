<?php

namespace App\Filament\Resources\SalesOrders;

use App\Filament\Concerns\HasPermissionAccess;
use App\Filament\Resources\SalesOrders\Pages\CreateSalesOrder;
use App\Filament\Resources\SalesOrders\Pages\EditSalesOrder;
use App\Filament\Resources\SalesOrders\Pages\ListSalesOrders;
use App\Filament\Resources\SalesOrders\Pages\ViewSalesOrder;
use App\Filament\Resources\SalesOrders\Schemas\SalesOrderForm;
use App\Filament\Resources\SalesOrders\Tables\SalesOrdersTable;
use App\Models\SalesOrder;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SalesOrderResource extends Resource
{
    use HasPermissionAccess;

    protected static ?string $model = SalesOrder::class;

    public static function getNavigationGroup(): string|null
    {
        return '📈 Sales & CRM';
    }

    protected static ?string $label = 'Sales Order';

    protected static ?string $pluralLabel = 'Sales Order';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingCart;

    protected static ?int $navigationSort = 411;

    protected static ?string $recordTitleAttribute = 'so_number';

    public static function form(Schema $schema): Schema
    {
        return SalesOrderForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SalesOrdersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSalesOrders::route('/'),
            'create' => CreateSalesOrder::route('/create'),
            'edit' => EditSalesOrder::route('/{record}/edit'),
            'view' => ViewSalesOrder::route('/{record}'),
        ];
    }
}
