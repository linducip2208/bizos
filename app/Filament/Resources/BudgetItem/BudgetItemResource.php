<?php

namespace App\Filament\Resources\BudgetItem;

use App\Filament\Resources\BudgetItem\Pages\CreateBudgetItem;
use App\Filament\Resources\BudgetItem\Pages\EditBudgetItem;
use App\Filament\Resources\BudgetItem\Pages\ListBudgetItems;
use App\Filament\Resources\BudgetItem\Schemas\BudgetItemForm;
use App\Filament\Resources\BudgetItem\Tables\BudgetItemsTable;
use App\Models\BudgetItem;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Panel;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;


use App\Filament\Concerns\HasPermissionAccess;
class BudgetItemResource extends Resource
{
    use HasPermissionAccess;
    protected static ?string $model = BudgetItem::class;

    public static function getSlug(?Panel $panel = null): string
    {
        return 'budget-items';
    }

    public static function getNavigationGroup(): string|null
    {
        return '💵 Finance & Accounting';
    }

    protected static ?string $label = 'Item Anggaran';

    protected static ?string $pluralLabel = 'Item Anggaran';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQueueList;

    protected static ?int $navigationSort = 314;

    protected static ?string $recordTitleAttribute = 'description';

    public static function form(Schema $schema): Schema
    {
        return BudgetItemForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BudgetItemsTable::configure($table);
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
            'index' => ListBudgetItems::route('/'),
            'create' => CreateBudgetItem::route('/create'),
            'edit' => EditBudgetItem::route('/{record}/edit'),
        ];
    }
}