<?php

namespace App\Filament\Resources\StockBalances;

use App\Filament\Concerns\HasPermissionAccess;
use App\Filament\Resources\StockBalances\Pages\CreateStockBalance;
use App\Filament\Resources\StockBalances\Pages\EditStockBalance;
use App\Filament\Resources\StockBalances\Pages\ListStockBalances;
use App\Filament\Resources\StockBalances\Schemas\StockBalanceForm;
use App\Filament\Resources\StockBalances\Tables\StockBalanceTable;
use App\Models\StockBalance;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class StockBalanceResource extends Resource
{
    use HasPermissionAccess;

    protected static ?string $model = StockBalance::class;

    public static function getNavigationGroup(): string|null
    {
        return '?? Procurement';
    }

    protected static ?string $label = 'Saldo Stok';

    protected static ?string $pluralLabel = 'Saldo Stok';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedScale;

    protected static ?int $navigationSort = 110;

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return StockBalanceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StockBalanceTable::configure($table);
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
            'index' => ListStockBalances::route('/'),
            'create' => CreateStockBalance::route('/create'),
            'edit' => EditStockBalance::route('/{record}/edit'),
        ];
    }
}