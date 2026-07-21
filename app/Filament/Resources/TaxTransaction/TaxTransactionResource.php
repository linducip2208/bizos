<?php

namespace App\Filament\Resources\TaxTransaction;

use App\Filament\Resources\TaxTransaction\Pages\CreateTaxTransaction;
use App\Filament\Resources\TaxTransaction\Pages\EditTaxTransaction;
use App\Filament\Resources\TaxTransaction\Pages\ListTaxTransactions;
use App\Filament\Resources\TaxTransaction\Schemas\TaxTransactionForm;
use App\Filament\Resources\TaxTransaction\Tables\TaxTransactionsTable;
use App\Models\TaxTransaction;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Panel;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;


use App\Filament\Concerns\HasPermissionAccess;
class TaxTransactionResource extends Resource
{
    use HasPermissionAccess;
    protected static ?string $model = TaxTransaction::class;

    public static function getSlug(?Panel $panel = null): string
    {
        return 'tax-transactions';
    }

    public static function getNavigationGroup(): string|null
    {
        return '?? Finance & Accounting';
    }

    protected static ?string $label = 'Transaksi Pajak';

    protected static ?string $pluralLabel = 'Transaksi Pajak';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedReceiptPercent;

    protected static ?int $navigationSort = 315;

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return TaxTransactionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TaxTransactionsTable::configure($table);
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
            'index' => ListTaxTransactions::route('/'),
            'create' => CreateTaxTransaction::route('/create'),
            'edit' => EditTaxTransaction::route('/{record}/edit'),
        ];
    }
}