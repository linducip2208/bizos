<?php

namespace App\Filament\Resources\PurchaseRequisitions;

use App\Filament\Concerns\HasPermissionAccess;
use App\Filament\Resources\PurchaseRequisitions\Pages\CreatePurchaseRequisition;
use App\Filament\Resources\PurchaseRequisitions\Pages\EditPurchaseRequisition;
use App\Filament\Resources\PurchaseRequisitions\Pages\ListPurchaseRequisitions;
use App\Filament\Resources\PurchaseRequisitions\RelationManagers\PurchaseRequisitionItemsRelationManager;
use App\Filament\Resources\PurchaseRequisitions\Schemas\PurchaseRequisitionForm;
use App\Filament\Resources\PurchaseRequisitions\Tables\PurchaseRequisitionTable;
use App\Models\PurchaseRequisition;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PurchaseRequisitionResource extends Resource
{
    use HasPermissionAccess;

    protected static ?string $model = PurchaseRequisition::class;

    public static function getNavigationGroup(): string|null
    {
        return '📦 Product & Inventory';
    }

    protected static ?string $label = 'Permintaan Pembelian';

    protected static ?string $pluralLabel = 'Permintaan Pembelian';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static ?int $navigationSort = 103;

    protected static ?string $recordTitleAttribute = 'pr_number';

    public static function form(Schema $schema): Schema
    {
        return PurchaseRequisitionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PurchaseRequisitionTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            PurchaseRequisitionItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPurchaseRequisitions::route('/'),
            'create' => CreatePurchaseRequisition::route('/create'),
            'edit' => EditPurchaseRequisition::route('/{record}/edit'),
        ];
    }
}