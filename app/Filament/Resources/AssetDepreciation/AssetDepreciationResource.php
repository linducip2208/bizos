<?php

namespace App\Filament\Resources\AssetDepreciation;

use App\Filament\Resources\AssetDepreciation\Pages\CreateAssetDepreciation;
use App\Filament\Resources\AssetDepreciation\Pages\EditAssetDepreciation;
use App\Filament\Resources\AssetDepreciation\Pages\ListAssetDepreciations;
use App\Filament\Resources\AssetDepreciation\Schemas\AssetDepreciationForm;
use App\Filament\Resources\AssetDepreciation\Tables\AssetDepreciationsTable;
use App\Models\AssetDepreciation;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Panel;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;


use App\Filament\Concerns\HasPermissionAccess;
class AssetDepreciationResource extends Resource
{
    use HasPermissionAccess;
    protected static ?string $model = AssetDepreciation::class;

    public static function getSlug(?Panel $panel = null): string
    {
        return 'asset-depreciations';
    }

    public static function getNavigationGroup(): string|null
    {
        return '?? Finance & Accounting';
    }

    protected static ?string $label = 'Penyusutan Aset';

    protected static ?string $pluralLabel = 'Penyusutan Aset';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowTrendingDown;

    protected static ?int $navigationSort = 316;

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return AssetDepreciationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AssetDepreciationsTable::configure($table);
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
            'index' => ListAssetDepreciations::route('/'),
            'create' => CreateAssetDepreciation::route('/create'),
            'edit' => EditAssetDepreciation::route('/{record}/edit'),
        ];
    }
}