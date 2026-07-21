<?php

namespace App\Filament\Resources\Currency;

use App\Filament\Resources\Currency\Pages\CreateCurrency;
use App\Filament\Resources\Currency\Pages\EditCurrency;
use App\Filament\Resources\Currency\Pages\ListCurrencies;
use App\Filament\Resources\Currency\Schemas\CurrencyForm;
use App\Filament\Resources\Currency\Tables\CurrenciesTable;
use App\Models\Currency;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

use App\Filament\Concerns\HasPermissionAccess;
use Filament\Panel;

class CurrencyResource extends Resource
{
    use HasPermissionAccess;

    protected static ?string $model = Currency::class;

    public static function getSlug(?Panel $panel = null): string
    {
        return 'currencies';
    }

    public static function getNavigationGroup(): string|null
    {
        return '💵 Finance & Accounting';
    }

    protected static ?string $label = 'Mata Uang';

    protected static ?string $pluralLabel = 'Mata Uang';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCurrencyDollar;

    protected static ?int $navigationSort = 320;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return CurrencyForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CurrenciesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCurrencies::route('/'),
            'create' => CreateCurrency::route('/create'),
            'edit' => EditCurrency::route('/{record}/edit'),
        ];
    }
}