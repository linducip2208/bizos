<?php

namespace App\Filament\Resources\ReimbursementCategories;

use App\Filament\Resources\ReimbursementCategories\Pages\CreateReimbursementCategory;
use App\Filament\Resources\ReimbursementCategories\Pages\EditReimbursementCategory;
use App\Filament\Resources\ReimbursementCategories\Pages\ListReimbursementCategories;
use App\Filament\Resources\ReimbursementCategories\Schemas\ReimbursementCategoryForm;
use App\Filament\Resources\ReimbursementCategories\Tables\ReimbursementCategoriesTable;
use App\Models\ReimbursementCategory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;


use App\Filament\Concerns\HasPermissionAccess;
class ReimbursementCategoryResource extends Resource
{
    use HasPermissionAccess;
    protected static ?string $model = ReimbursementCategory::class;

    public static function getNavigationGroup(): string|null
    {
        return '?? HR & Payroll';
    }

    protected static ?string $label = 'Kategori Reimbursement';

    protected static ?string $pluralLabel = 'Kategori Reimbursement';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static ?int $navigationSort = 103;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return ReimbursementCategoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ReimbursementCategoriesTable::configure($table);
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
            'index' => ListReimbursementCategories::route('/'),
            'create' => CreateReimbursementCategory::route('/create'),
            'edit' => EditReimbursementCategory::route('/{record}/edit'),
        ];
    }
}