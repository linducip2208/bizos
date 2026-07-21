<?php

namespace App\Filament\Resources\Allowance;

use App\Filament\Resources\Allowance\Pages\CreateAllowance;
use App\Filament\Resources\Allowance\Pages\EditAllowance;
use App\Filament\Resources\Allowance\Pages\ListAllowances;
use App\Filament\Resources\Allowance\Schemas\AllowanceForm;
use App\Filament\Resources\Allowance\Tables\AllowancesTable;
use App\Models\Allowance;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Panel;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

use App\Filament\Concerns\HasPermissionAccess;

class AllowanceResource extends Resource
{
    use HasPermissionAccess;

    // Gunakan SalaryComponent dengan component_category = 'allowance'.
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $model = Allowance::class;

    public static function getSlug(?Panel $panel = null): string
    {
        return 'allowances';
    }

    public static function getNavigationGroup(): string|null
    {
        return '💰 Payroll';
    }

    protected static ?string $label = 'Tunjangan';

    protected static ?string $pluralLabel = 'Tunjangan';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedGift;

    protected static ?int $navigationSort = 210;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return AllowanceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AllowancesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAllowances::route('/'),
            'create' => CreateAllowance::route('/create'),
            'edit' => EditAllowance::route('/{record}/edit'),
        ];
    }
}
