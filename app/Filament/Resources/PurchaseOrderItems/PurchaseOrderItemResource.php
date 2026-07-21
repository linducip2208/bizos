<?php

namespace App\Filament\Resources\PurchaseOrderItems;

use App\Filament\Concerns\HasPermissionAccess;
use App\Filament\Resources\PurchaseOrderItems\Pages\CreatePurchaseOrderItem;
use App\Filament\Resources\PurchaseOrderItems\Pages\EditPurchaseOrderItem;
use App\Filament\Resources\PurchaseOrderItems\Pages\ListPurchaseOrderItems;
use App\Filament\Resources\PurchaseOrderItems\Schemas\PurchaseOrderItemForm;
use App\Filament\Resources\PurchaseOrderItems\Tables\PurchaseOrderItemTable;
use App\Models\PurchaseOrderItem;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PurchaseOrderItemResource extends Resource
{
    use HasPermissionAccess;

    protected static ?string $model = PurchaseOrderItem::class;

    public static function getNavigationGroup(): string|null
    {
        return 'Procurement & Inventory';
    }

    protected static ?string $label = 'Item PO';

    protected static ?string $pluralLabel = 'Item PO';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQueueList;

    protected static ?int $navigationSort = 106;

    protected static ?string $recordTitleAttribute = 'item_name';

    public static function form(Schema $schema): Schema
    {
        return PurchaseOrderItemForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PurchaseOrderItemTable::configure($table);
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
            'index' => ListPurchaseOrderItems::route('/'),
            'create' => CreatePurchaseOrderItem::route('/create'),
            'edit' => EditPurchaseOrderItem::route('/{record}/edit'),
        ];
    }
}