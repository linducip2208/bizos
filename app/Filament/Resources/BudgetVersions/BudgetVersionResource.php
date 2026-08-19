<?php

namespace App\Filament\Resources\BudgetVersions;

use App\Filament\Resources\BudgetVersions\Pages\CreateBudgetVersion;
use App\Filament\Resources\BudgetVersions\Pages\EditBudgetVersion;
use App\Filament\Resources\BudgetVersions\Pages\ListBudgetVersions;
use App\Filament\Resources\BudgetVersions\Schemas\BudgetVersionForm;
use App\Filament\Resources\BudgetVersions\Tables\BudgetVersionsTable;
use App\Models\BudgetVersion;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Panel;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use App\Filament\Concerns\HasPermissionAccess;

class BudgetVersionResource extends Resource
{
    use HasPermissionAccess;

    protected static ?string $model = BudgetVersion::class;

    public static function getSlug(?Panel $panel = null): string
    {
        return 'Finance & Accounting';
    }

    public static function getNavigationGroup(): string|null
    {
        return 'Finance & Accounting';
    }

    protected static ?string $label = 'Budget Versions';

    protected static ?string $pluralLabel = 'Budget Versions';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentDuplicate;

    protected static ?int $navigationSort = 307;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return BudgetVersionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BudgetVersionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBudgetVersions::route('/'),
            'create' => CreateBudgetVersion::route('/create'),
            'edit' => EditBudgetVersion::route('/{record}/edit'),
        ];
    }
}
