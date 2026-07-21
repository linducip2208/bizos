<?php

namespace App\Filament\Resources\PosTransactionItems;

use App\Filament\Resources\PosTransactionItems\Pages\CreatePosTransactionItem;
use App\Filament\Resources\PosTransactionItems\Pages\EditPosTransactionItem;
use App\Filament\Resources\PosTransactionItems\Pages\ListPosTransactionItems;
use App\Filament\Resources\PosTransactionItems\Schemas\PosTransactionItemForm;
use App\Filament\Resources\PosTransactionItems\Tables\PosTransactionItemsTable;
use App\Models\PosTransactionItem;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;


use App\Filament\Concerns\HasPermissionAccess;
class PosTransactionItemResource extends Resource
{
    use HasPermissionAccess;
    protected static ?string $model = PosTransactionItem::class;

    public static function getNavigationGroup(): string|null
    {
        return 'POS';
    }

    protected static ?string $label = 'Item Transaksi POS';

    protected static ?string $pluralLabel = 'Item Transaksi POS';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQueueList;

    protected static ?int $navigationSort = 609;

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return PosTransactionItemForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PosTransactionItemsTable::configure($table);
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
            'index' => ListPosTransactionItems::route('/'),
            'create' => CreatePosTransactionItem::route('/create'),
            'edit' => EditPosTransactionItem::route('/{record}/edit'),
        ];
    }
}