<?php

namespace App\Filament\Resources\CanteenOrderItems;

use App\Filament\Resources\CanteenOrderItems\Pages\CreateCanteenOrderItem;
use App\Filament\Resources\CanteenOrderItems\Pages\EditCanteenOrderItem;
use App\Filament\Resources\CanteenOrderItems\Pages\ListCanteenOrderItems;
use App\Filament\Resources\CanteenOrderItems\Schemas\CanteenOrderItemForm;
use App\Filament\Resources\CanteenOrderItems\Tables\CanteenOrderItemsTable;
use App\Models\CanteenOrderItem;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;


use App\Filament\Concerns\HasPermissionAccess;
class CanteenOrderItemResource extends Resource
{
    use HasPermissionAccess;
    protected static ?string $model = CanteenOrderItem::class;

    public static function getNavigationGroup(): string|null
    {
        return '?? HR & Payroll';
    }

    protected static ?string $label = 'Item Pesanan Kantin';

    protected static ?string $pluralLabel = 'Item Pesanan Kantin';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedListBullet;

    protected static ?int $navigationSort = 122;

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return CanteenOrderItemForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CanteenOrderItemsTable::configure($table);
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
            'index' => ListCanteenOrderItems::route('/'),
            'create' => CreateCanteenOrderItem::route('/create'),
            'edit' => EditCanteenOrderItem::route('/{record}/edit'),
        ];
    }
}