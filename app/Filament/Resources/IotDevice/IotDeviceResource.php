<?php

namespace App\Filament\Resources\IotDevice;

use App\Filament\Resources\IotDevice\Pages\CreateIotDevice;
use App\Filament\Resources\IotDevice\Pages\EditIotDevice;
use App\Filament\Resources\IotDevice\Pages\ListIotDevices;
use App\Filament\Resources\IotDevice\Schemas\IotDeviceForm;
use App\Filament\Resources\IotDevice\Tables\IotDevicesTable;
use App\Filament\Concerns\HasPermissionAccess;
use App\Models\IotDevice;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Panel;

class IotDeviceResource extends Resource
{
    use HasPermissionAccess;

    protected static ?string $model = IotDevice::class;

    public static function getSlug(?Panel $panel = null): string
    {
        return 'iot-devices';
    }

    public static function getNavigationGroup(): string|null
    {
        return 'IoT & Sensor';
    }

    protected static ?string $label = 'Perangkat IoT';

    protected static ?string $pluralLabel = 'Perangkat IoT';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCpuChip;

    protected static ?int $navigationSort = 1601;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return IotDeviceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return IotDevicesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListIotDevices::route('/'),
            'create' => CreateIotDevice::route('/create'),
            'edit' => EditIotDevice::route('/{record}/edit'),
        ];
    }
}