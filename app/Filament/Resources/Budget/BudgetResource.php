<?php

namespace App\Filament\Resources\Budget;

use App\Filament\Resources\Budget\Pages\CreateBudget;
use App\Filament\Resources\Budget\Pages\EditBudget;
use App\Filament\Resources\Budget\Pages\ListBudgets;
use App\Filament\Resources\Budget\Schemas\BudgetForm;
use App\Filament\Resources\Budget\Tables\BudgetsTable;
use App\Models\Budget;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Panel;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;


use App\Filament\Concerns\HasPermissionAccess;
class BudgetResource extends Resource
{
    use HasPermissionAccess;
    protected static ?string $model = Budget::class;

    public static function getSlug(?Panel $panel = null): string
    {
        return 'budgets';
    }

    public static function getNavigationGroup(): string|null
    {
        return 'Finance';
    }

    protected static ?string $label = 'Anggaran';

    protected static ?string $pluralLabel = 'Anggaran';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartPie;

    protected static ?int $navigationSort = 306;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return BudgetForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BudgetsTable::configure($table);
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
            'index' => ListBudgets::route('/'),
            'create' => CreateBudget::route('/create'),
            'edit' => EditBudget::route('/{record}/edit'),
        ];
    }
}