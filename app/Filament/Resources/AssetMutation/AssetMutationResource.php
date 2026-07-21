<?php

namespace App\Filament\Resources\AssetMutation;

use App\Filament\Resources\AssetMutation\Pages\CreateAssetMutation;
use App\Filament\Resources\AssetMutation\Pages\EditAssetMutation;
use App\Filament\Resources\AssetMutation\Pages\ListAssetMutations;
use App\Filament\Resources\AssetMutation\Schemas\AssetMutationForm;
use App\Filament\Resources\AssetMutation\Tables\AssetMutationsTable;
use App\Models\AssetMutation;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Panel;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;


use App\Filament\Concerns\HasPermissionAccess;
class AssetMutationResource extends Resource
{
    use HasPermissionAccess;
    protected static ?string $model = AssetMutation::class;

    public static function getSlug(?Panel $panel = null): string
    {
        return 'asset-mutations';
    }

    public static function getNavigationGroup(): string|null
    {
        return 'Finance';
    }

    protected static ?string $label = 'Mutasi Aset';

    protected static ?string $pluralLabel = 'Mutasi Aset';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowsRightLeft;

    protected static ?int $navigationSort = 317;

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return AssetMutationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AssetMutationsTable::configure($table);
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
            'index' => ListAssetMutations::route('/'),
            'create' => CreateAssetMutation::route('/create'),
            'edit' => EditAssetMutation::route('/{record}/edit'),
        ];
    }
}