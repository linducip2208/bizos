<?php

namespace App\Filament\Resources\PurchaseRequisitionItems;

use App\Filament\Concerns\HasPermissionAccess;
use App\Filament\Resources\PurchaseRequisitionItems\Pages\CreatePurchaseRequisitionItem;
use App\Filament\Resources\PurchaseRequisitionItems\Pages\EditPurchaseRequisitionItem;
use App\Filament\Resources\PurchaseRequisitionItems\Pages\ListPurchaseRequisitionItems;
use App\Filament\Resources\PurchaseRequisitionItems\Schemas\PurchaseRequisitionItemForm;
use App\Filament\Resources\PurchaseRequisitionItems\Tables\PurchaseRequisitionItemTable;
use App\Models\PurchaseRequisitionItem;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PurchaseRequisitionItemResource extends Resource
{
    use HasPermissionAccess;

    protected static ?string $model = PurchaseRequisitionItem::class;

    public static function getNavigationGroup(): string|null
    {
        return 'Procurement & Inventory';
    }

    protected static ?string $label = 'Item PR';

    protected static ?string $pluralLabel = 'Item PR';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?int $navigationSort = 104;

    protected static ?string $recordTitleAttribute = 'item_name';

    public static function form(Schema $schema): Schema
    {
        return PurchaseRequisitionItemForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PurchaseRequisitionItemTable::configure($table);
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
            'index' => ListPurchaseRequisitionItems::route('/'),
            'create' => CreatePurchaseRequisitionItem::route('/create'),
            'edit' => EditPurchaseRequisitionItem::route('/{record}/edit'),
        ];
    }
}