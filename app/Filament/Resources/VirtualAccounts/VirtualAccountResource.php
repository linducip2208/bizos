<?php

namespace App\Filament\Resources\VirtualAccounts;

use App\Filament\Concerns\HasPermissionAccess;
use App\Filament\Resources\VirtualAccounts\Pages\CreateVirtualAccount;
use App\Filament\Resources\VirtualAccounts\Pages\EditVirtualAccount;
use App\Filament\Resources\VirtualAccounts\Pages\ListVirtualAccounts;
use App\Filament\Resources\VirtualAccounts\Schemas\VirtualAccountForm;
use App\Filament\Resources\VirtualAccounts\Tables\VirtualAccountsTable;
use App\Models\VirtualAccount;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class VirtualAccountResource extends Resource
{
    use HasPermissionAccess;

    protected static ?string $model = VirtualAccount::class;

    public static function getNavigationGroup(): string|null
    {
        return 'Hub Integrasi';
    }

    protected static ?string $label = 'Virtual Account';

    protected static ?string $pluralLabel = 'Virtual Account';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;

    protected static ?int $navigationSort = 8;

    public static function form(Schema $schema): Schema
    {
        return VirtualAccountForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return VirtualAccountsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListVirtualAccounts::route('/'),
            'create' => CreateVirtualAccount::route('/create'),
            'edit' => EditVirtualAccount::route('/{record}/edit'),
        ];
    }
}