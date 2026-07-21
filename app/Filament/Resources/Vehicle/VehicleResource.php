<?php

namespace App\Filament\Resources\Vehicle;

use App\Filament\Resources\Vehicle\Pages\CreateVehicle;
use App\Filament\Resources\Vehicle\Pages\EditVehicle;
use App\Filament\Resources\Vehicle\Pages\ListVehicles;
use App\Filament\Resources\Vehicle\Schemas\VehicleForm;
use App\Filament\Resources\Vehicle\Tables\VehicleTable;
use App\Models\Vehicle;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use App\Filament\Concerns\HasPermissionAccess;

class VehicleResource extends Resource
{
    use HasPermissionAccess;

    protected static ?string $model = Vehicle::class;

    public static function getNavigationGroup(): string|null
    {
        return '?? Master Data';
    }

    protected static ?string $label = 'Kendaraan';

    protected static ?string $pluralLabel = 'Kendaraan';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTruck;

    protected static ?int $navigationSort = 8;

    protected static ?string $recordTitleAttribute = 'plate_number';

    public static function form(Schema $schema): Schema
    {
        return VehicleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return VehicleTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListVehicles::route('/'),
            'create' => CreateVehicle::route('/create'),
            'edit' => EditVehicle::route('/{record}/edit'),
        ];
    }
}