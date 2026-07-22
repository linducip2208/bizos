<?php

namespace App\Filament\Resources\GoodsReceiptItems;

use App\Filament\Concerns\HasPermissionAccess;
use App\Filament\Resources\GoodsReceiptItems\Pages\CreateGoodsReceiptItem;
use App\Filament\Resources\GoodsReceiptItems\Pages\EditGoodsReceiptItem;
use App\Filament\Resources\GoodsReceiptItems\Pages\ListGoodsReceiptItems;
use App\Filament\Resources\GoodsReceiptItems\Schemas\GoodsReceiptItemForm;
use App\Filament\Resources\GoodsReceiptItems\Tables\GoodsReceiptItemTable;
use App\Models\GoodsReceiptItem;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class GoodsReceiptItemResource extends Resource
{
    protected static bool $shouldRegisterNavigation = false;
    use HasPermissionAccess;

    protected static ?string $model = GoodsReceiptItem::class;

    public static function getNavigationGroup(): string|null
    {
        return '📦 Product & Inventory';
    }

    protected static ?string $label = 'Item Penerimaan';

    protected static ?string $pluralLabel = 'Item Penerimaan';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static ?int $navigationSort = 108;

    protected static ?string $recordTitleAttribute = 'item_name';

    public static function form(Schema $schema): Schema
    {
        return GoodsReceiptItemForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return GoodsReceiptItemTable::configure($table);
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
            'index' => ListGoodsReceiptItems::route('/'),
            'create' => CreateGoodsReceiptItem::route('/create'),
            'edit' => EditGoodsReceiptItem::route('/{record}/edit'),
        ];
    }
}