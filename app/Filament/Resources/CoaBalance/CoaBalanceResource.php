<?php

namespace App\Filament\Resources\CoaBalance;

use App\Filament\Resources\CoaBalance\Pages\CreateCoaBalance;
use App\Filament\Resources\CoaBalance\Pages\EditCoaBalance;
use App\Filament\Resources\CoaBalance\Pages\ListCoaBalances;
use App\Filament\Resources\CoaBalance\Schemas\CoaBalanceForm;
use App\Filament\Resources\CoaBalance\Tables\CoaBalancesTable;
use App\Models\CoaBalance;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Panel;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;


use App\Filament\Concerns\HasPermissionAccess;
class CoaBalanceResource extends Resource
{
    use HasPermissionAccess;
    protected static ?string $model = CoaBalance::class;

    public static function getSlug(?Panel $panel = null): string
    {
        return 'coa-balances';
    }

    public static function getNavigationGroup(): string|null
    {
        return 'Finance';
    }

    protected static ?string $label = 'Saldo COA';

    protected static ?string $pluralLabel = 'Saldo COA';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedScale;

    protected static ?int $navigationSort = 307;

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return CoaBalanceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CoaBalancesTable::configure($table);
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
            'index' => ListCoaBalances::route('/'),
            'create' => CreateCoaBalance::route('/create'),
            'edit' => EditCoaBalance::route('/{record}/edit'),
        ];
    }
}