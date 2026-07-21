<?php

namespace App\Filament\Resources\BankTransaction;

use App\Filament\Resources\BankTransaction\Pages\CreateBankTransaction;
use App\Filament\Resources\BankTransaction\Pages\EditBankTransaction;
use App\Filament\Resources\BankTransaction\Pages\ListBankTransactions;
use App\Filament\Resources\BankTransaction\Schemas\BankTransactionForm;
use App\Filament\Resources\BankTransaction\Tables\BankTransactionsTable;
use App\Models\BankTransaction;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

use App\Filament\Concerns\HasPermissionAccess;
use Filament\Panel;

class BankTransactionResource extends Resource
{
    use HasPermissionAccess;

    protected static ?string $model = BankTransaction::class;

    public static function getSlug(?Panel $panel = null): string
    {
        return 'bank-transactions';
    }

    public static function getNavigationGroup(): string|null
    {
        return '?? Finance & Accounting';
    }

    protected static ?string $label = 'Transaksi Bank';

    protected static ?string $pluralLabel = 'Transaksi Bank';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static ?int $navigationSort = 323;

    protected static ?string $recordTitleAttribute = 'description';

    public static function form(Schema $schema): Schema
    {
        return BankTransactionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BankTransactionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBankTransactions::route('/'),
            'create' => CreateBankTransaction::route('/create'),
            'edit' => EditBankTransaction::route('/{record}/edit'),
        ];
    }
}