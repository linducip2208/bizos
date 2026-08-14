<?php

namespace App\Filament\Resources\CashDenominations;

use App\Filament\Concerns\HasPermissionAccess;
use App\Filament\Resources\CashDenominations\Pages\CreateCashDenomination;
use App\Filament\Resources\CashDenominations\Pages\EditCashDenomination;
use App\Filament\Resources\CashDenominations\Pages\ListCashDenominations;
use App\Filament\Resources\CashDenominations\Schemas\CashDenominationForm;
use App\Filament\Resources\CashDenominations\Tables\CashDenominationTable;
use App\Models\CashDenomination;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CashDenominationResource extends Resource
{
    use HasPermissionAccess;

    protected static ?string $model = CashDenomination::class;

    public static function getNavigationGroup(): string|null
    {
        return '🛒 POS & Retail';
    }

    protected static ?string $label = 'Pecahan Uang';

    protected static ?string $pluralLabel = 'Pecahan Uang';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static ?int $navigationSort = 603;

    protected static ?string $recordTitleAttribute = 'label';

    public static function form(Schema $schema): Schema
    {
        return CashDenominationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CashDenominationTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCashDenominations::route('/'),
            'create' => CreateCashDenomination::route('/create'),
            'edit' => EditCashDenomination::route('/{record}/edit'),
        ];
    }
}
