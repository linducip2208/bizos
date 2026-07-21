<?php

namespace App\Filament\Resources\LoyaltyTransactions;

use App\Filament\Resources\LoyaltyTransactions\Pages\ListLoyaltyTransactions;
use App\Filament\Resources\LoyaltyTransactions\Tables\LoyaltyTransactionTable;
use App\Models\LoyaltyTransaction;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

use App\Filament\Concerns\HasPermissionAccess;

class LoyaltyTransactionResource extends Resource
{
    use HasPermissionAccess;

    protected static ?string $model = LoyaltyTransaction::class;

    public static function getNavigationGroup(): string|null
    {
        return '🛒 POS & Retail';
    }

    protected static ?string $label = 'Transaksi Loyalitas';

    protected static ?string $pluralLabel = 'Transaksi Loyalitas';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowTrendingUp;

    protected static ?int $navigationSort = 613;

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return $schema;
    }

    public static function table(Table $table): Table
    {
        return LoyaltyTransactionTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLoyaltyTransactions::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}