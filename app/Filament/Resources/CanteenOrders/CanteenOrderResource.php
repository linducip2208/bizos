<?php

namespace App\Filament\Resources\CanteenOrders;

use App\Filament\Resources\CanteenOrders\Pages\CreateCanteenOrder;
use App\Filament\Resources\CanteenOrders\Pages\EditCanteenOrder;
use App\Filament\Resources\CanteenOrders\Pages\ListCanteenOrders;
use App\Filament\Resources\CanteenOrders\Schemas\CanteenOrderForm;
use App\Filament\Resources\CanteenOrders\Tables\CanteenOrdersTable;
use App\Models\CanteenOrder;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;


use App\Filament\Concerns\HasPermissionAccess;
class CanteenOrderResource extends Resource
{
    use HasPermissionAccess;
    protected static ?string $model = CanteenOrder::class;

    public static function getNavigationGroup(): string|null
    {
        return '?? HR & Payroll';
    }

    protected static ?string $label = 'Pesanan Kantin';

    protected static ?string $pluralLabel = 'Pesanan Kantin';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static ?int $navigationSort = 121;

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return CanteenOrderForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CanteenOrdersTable::configure($table);
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
            'index' => ListCanteenOrders::route('/'),
            'create' => CreateCanteenOrder::route('/create'),
            'edit' => EditCanteenOrder::route('/{record}/edit'),
        ];
    }
}