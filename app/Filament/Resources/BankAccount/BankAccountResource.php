<?php

namespace App\Filament\Resources\BankAccount;

use App\Filament\Resources\BankAccount\Pages\CreateBankAccount;
use App\Filament\Resources\BankAccount\Pages\EditBankAccount;
use App\Filament\Resources\BankAccount\Pages\ListBankAccounts;
use App\Filament\Resources\BankAccount\Schemas\BankAccountForm;
use App\Filament\Resources\BankAccount\Tables\BankAccountsTable;
use App\Models\BankAccount;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

use App\Filament\Concerns\HasPermissionAccess;
use Filament\Panel;

class BankAccountResource extends Resource
{
    use HasPermissionAccess;

    protected static ?string $model = BankAccount::class;

    public static function getSlug(?Panel $panel = null): string
    {
        return 'bank-accounts';
    }

    public static function getNavigationGroup(): string|null
    {
        return '?? Finance & Accounting';
    }

    protected static ?string $label = 'Rekening Bank';

    protected static ?string $pluralLabel = 'Rekening Bank';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingLibrary;

    protected static ?int $navigationSort = 322;

    protected static ?string $recordTitleAttribute = 'account_name';

    public static function form(Schema $schema): Schema
    {
        return BankAccountForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BankAccountsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBankAccounts::route('/'),
            'create' => CreateBankAccount::route('/create'),
            'edit' => EditBankAccount::route('/{record}/edit'),
        ];
    }
}