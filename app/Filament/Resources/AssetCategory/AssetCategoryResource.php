<?php

namespace App\Filament\Resources\AssetCategory;

use App\Filament\Resources\AssetCategory\Pages\CreateAssetCategory;
use App\Filament\Resources\AssetCategory\Pages\EditAssetCategory;
use App\Filament\Resources\AssetCategory\Pages\ListAssetCategories;
use App\Filament\Resources\AssetCategory\Schemas\AssetCategoryForm;
use App\Filament\Resources\AssetCategory\Tables\AssetCategoriesTable;
use App\Models\AssetCategory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Panel;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;


use App\Filament\Concerns\HasPermissionAccess;
class AssetCategoryResource extends Resource
{
    use HasPermissionAccess;
    protected static ?string $model = AssetCategory::class;

    public static function getSlug(?Panel $panel = null): string
    {
        return 'asset-categories';
    }

    public static function getNavigationGroup(): string|null
    {
        return 'Finance';
    }

    protected static ?string $label = 'Kategori Aset';

    protected static ?string $pluralLabel = 'Kategori Aset';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCpuChip;

    protected static ?int $navigationSort = 305;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return AssetCategoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AssetCategoriesTable::configure($table);
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
            'index' => ListAssetCategories::route('/'),
            'create' => CreateAssetCategory::route('/create'),
            'edit' => EditAssetCategory::route('/{record}/edit'),
        ];
    }
}
