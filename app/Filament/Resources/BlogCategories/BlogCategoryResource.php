<?php

namespace App\Filament\Resources\BlogCategories;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use App\Filament\Concerns\HasPermissionAccess;
use App\Filament\Resources\BlogCategories\Pages\ListBlogCategories;
use App\Filament\Resources\BlogCategories\Pages\CreateBlogCategory;
use App\Filament\Resources\BlogCategories\Pages\EditBlogCategory;
use App\Filament\Resources\BlogCategories\Schemas\BlogCategoryForm;
use App\Filament\Resources\BlogCategories\Tables\BlogCategoryTable;
use App\Models\BlogCategory;

class BlogCategoryResource extends Resource
{
    use HasPermissionAccess;
    protected static ?string $model = BlogCategory::class;

    public static function getNavigationGroup(): string|null
    {
        return 'Marketing';
    }

    protected static ?string $label = 'Blog Categories';
    protected static ?string $pluralLabel = 'Kategori Blog';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowTurnLeftUp;
    protected static ?int $navigationSort = 1400;
    protected static ?string $recordTitleAttribute = 'name';
    public static function form(Schema $schema): Schema { return BlogCategoryForm::configure($schema); }
    public static function table(Table $table): Table { return BlogCategoryTable::configure($table); }
    public static function getRelations(): array { return []; }
    public static function getPages(): array { return [
        'index' => ListBlogCategories::route('/'),
        'create' => CreateBlogCategory::route('/create'),
        'edit' => EditBlogCategory::route('/{record}/edit'),
    ];}
}
