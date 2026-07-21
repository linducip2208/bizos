<?php

namespace App\Filament\Resources\Bonus;

use App\Filament\Resources\Bonus\Pages\CreateBonus;
use App\Filament\Resources\Bonus\Pages\EditBonus;
use App\Filament\Resources\Bonus\Pages\ListBonuses;
use App\Filament\Resources\Bonus\Schemas\BonusForm;
use App\Filament\Resources\Bonus\Tables\BonusesTable;
use App\Models\Bonus;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Panel;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

use App\Filament\Concerns\HasPermissionAccess;

class BonusResource extends Resource
{
    use HasPermissionAccess;
    protected static ?string $model = Bonus::class;

    public static function getSlug(?Panel $panel = null): string
    {
        return 'bonuses';
    }

    public static function getNavigationGroup(): string|null
    {
        return '💰 Payroll';
    }

    protected static ?string $label = 'Bonus';

    protected static ?string $pluralLabel = 'Bonus';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedStar;

    protected static ?int $navigationSort = 212;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return BonusForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BonusesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBonuses::route('/'),
            'create' => CreateBonus::route('/create'),
            'edit' => EditBonus::route('/{record}/edit'),
        ];
    }
}
