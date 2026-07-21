<?php

namespace App\Filament\Resources\AssetMaintenance;

use App\Filament\Resources\AssetMaintenance\Pages\CreateAssetMaintenance;
use App\Filament\Resources\AssetMaintenance\Pages\EditAssetMaintenance;
use App\Filament\Resources\AssetMaintenance\Pages\ListAssetMaintenances;
use App\Filament\Resources\AssetMaintenance\Schemas\AssetMaintenanceForm;
use App\Filament\Resources\AssetMaintenance\Tables\AssetMaintenancesTable;
use App\Models\AssetMaintenance;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Panel;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;


use App\Filament\Concerns\HasPermissionAccess;
class AssetMaintenanceResource extends Resource
{
    use HasPermissionAccess;
    protected static ?string $model = AssetMaintenance::class;

    public static function getSlug(?Panel $panel = null): string
    {
        return 'asset-maintenances';
    }

    public static function getNavigationGroup(): string|null
    {
        return 'Finance';
    }

    protected static ?string $label = 'Pemeliharaan Aset';

    protected static ?string $pluralLabel = 'Pemeliharaan Aset';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedWrench;

    protected static ?int $navigationSort = 318;

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return AssetMaintenanceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AssetMaintenancesTable::configure($table);
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
            'index' => ListAssetMaintenances::route('/'),
            'create' => CreateAssetMaintenance::route('/create'),
            'edit' => EditAssetMaintenance::route('/{record}/edit'),
        ];
    }
}