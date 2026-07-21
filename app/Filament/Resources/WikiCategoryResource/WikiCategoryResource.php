<?php

namespace App\Filament\Resources\WikiCategoryResource;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use App\Filament\Concerns\HasPermissionAccess;
use App\Filament\Resources\WikiCategoryResource\Pages\ListWikiCategories;
use App\Filament\Resources\WikiCategoryResource\Pages\CreateWikiCategory;
use App\Filament\Resources\WikiCategoryResource\Pages\EditWikiCategory;
use App\Filament\Resources\WikiCategoryResource\Schemas\WikiCategoryForm;
use App\Filament\Resources\WikiCategoryResource\Tables\WikiCategoryTable;
use App\Models\WikiCategory;

class WikiCategoryResource extends Resource
{
    use HasPermissionAccess;
    protected static ?string $model = WikiCategory::class;
    public static function getNavigationGroup(): string|null { return 'Kolaborasi'; }
    protected static ?string $label = 'Kategori Wiki';
    protected static ?string $pluralLabel = 'Kategori Wiki';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFolder;
    protected static ?int $navigationSort = 710;
    protected static ?string $recordTitleAttribute = 'name';
    public static function form(Schema $schema): Schema { return WikiCategoryForm::configure($schema); }
    public static function table(Table $table): Table { return WikiCategoryTable::configure($table); }
    public static function getRelations(): array { return []; }
    public static function getPages(): array { return [
        'index' => ListWikiCategories::route('/'),
        'create' => CreateWikiCategory::route('/create'),
        'edit' => EditWikiCategory::route('/{record}/edit'),
    ];}
}
