<?php

namespace App\Filament\Resources\PosTransactions;

use App\Filament\Resources\PosTransactions\Pages\CreatePosTransaction;
use App\Filament\Resources\PosTransactions\Pages\EditPosTransaction;
use App\Filament\Resources\PosTransactions\Pages\ListPosTransactions;
use App\Filament\Resources\PosTransactions\Schemas\PosTransactionForm;
use App\Filament\Resources\PosTransactions\Tables\PosTransactionsTable;
use App\Models\PosTransaction;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;


use App\Filament\Concerns\HasPermissionAccess;
class PosTransactionResource extends Resource
{
    use HasPermissionAccess;
    protected static ?string $model = PosTransaction::class;

    public static function getNavigationGroup(): string|null
    {
        return 'POS';
    }

    protected static ?string $label = 'Transaksi POS';

    protected static ?string $pluralLabel = 'Transaksi POS';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingCart;

    protected static ?int $navigationSort = 608;

    protected static ?string $recordTitleAttribute = 'receipt_number';

    public static function form(Schema $schema): Schema
    {
        return PosTransactionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PosTransactionsTable::configure($table);
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
            'index' => ListPosTransactions::route('/'),
            'create' => CreatePosTransaction::route('/create'),
            'edit' => EditPosTransaction::route('/{record}/edit'),
        ];
    }
}
