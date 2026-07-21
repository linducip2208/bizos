<?php

namespace App\Filament\Resources\Warehouse;

use App\Filament\Concerns\HasPermissionAccess;
use App\Filament\Resources\Warehouse\Pages\CreateWarehouse;
use App\Filament\Resources\Warehouse\Pages\EditWarehouse;
use App\Filament\Resources\Warehouse\Pages\ListWarehouses;
use App\Filament\Resources\Warehouse\Schemas\WarehouseForm;
use App\Filament\Resources\Warehouse\Tables\WarehouseTable;
use App\Models\Warehouse;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class WarehouseResource extends Resource
{
    use HasPermissionAccess;

    protected static ?string $model = Warehouse::class;

    public static function getNavigationGroup(): string|null
    {
        return 'Procurement & Inventory';
    }

    protected static ?string $label = 'Gudang';

    protected static ?string $pluralLabel = 'Gudang';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    protected static ?int $navigationSort = 101;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return WarehouseForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WarehouseTable::configure($table);
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
            'index' => ListWarehouses::route('/'),
            'create' => CreateWarehouse::route('/create'),
            'edit' => EditWarehouse::route('/{record}/edit'),
        ];
    }
}