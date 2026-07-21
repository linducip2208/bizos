<?php

namespace App\Filament\Resources\CoaCategory;

use App\Filament\Resources\CoaCategory\Pages\CreateCoaCategory;
use App\Filament\Resources\CoaCategory\Pages\EditCoaCategory;
use App\Filament\Resources\CoaCategory\Pages\ListCoaCategories;
use App\Filament\Resources\CoaCategory\Schemas\CoaCategoryForm;
use App\Filament\Resources\CoaCategory\Tables\CoaCategoriesTable;
use App\Models\CoaCategory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Panel;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;


use App\Filament\Concerns\HasPermissionAccess;
class CoaCategoryResource extends Resource
{
    use HasPermissionAccess;
    protected static ?string $model = CoaCategory::class;

    public static function getSlug(?Panel $panel = null): string
    {
        return 'coa-categories';
    }

    public static function getNavigationGroup(): string|null
    {
        return 'Finance';
    }

    protected static ?string $label = 'Kategori COA';

    protected static ?string $pluralLabel = 'Kategori COA';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFolder;

    protected static ?int $navigationSort = 301;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return CoaCategoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CoaCategoriesTable::configure($table);
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
            'index' => ListCoaCategories::route('/'),
            'create' => CreateCoaCategory::route('/create'),
            'edit' => EditCoaCategory::route('/{record}/edit'),
        ];
    }
}