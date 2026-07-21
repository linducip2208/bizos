<?php

namespace App\Filament\Resources\WifiAccessPoints;

use App\Filament\Resources\WifiAccessPoints\Pages\CreateWifiAccessPoint;
use App\Filament\Resources\WifiAccessPoints\Pages\EditWifiAccessPoint;
use App\Filament\Resources\WifiAccessPoints\Pages\ListWifiAccessPoints;
use App\Filament\Resources\WifiAccessPoints\Schemas\WifiAccessPointForm;
use App\Filament\Resources\WifiAccessPoints\Tables\WifiAccessPointsTable;
use App\Models\WifiAccessPoint;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;


use App\Filament\Concerns\HasPermissionAccess;
class WifiAccessPointResource extends Resource
{
    use HasPermissionAccess;
    protected static ?string $model = WifiAccessPoint::class;

    public static function getNavigationGroup(): string|null
    {
        return '👥 Human Capital';
    }

    protected static ?string $label = 'Titik Akses WiFi';

    protected static ?string $pluralLabel = 'Titik Akses WiFi';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedWifi;

    protected static ?int $navigationSort = 109;

    protected static ?string $recordTitleAttribute = 'ssid';

    public static function form(Schema $schema): Schema
    {
        return WifiAccessPointForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WifiAccessPointsTable::configure($table);
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
            'index' => ListWifiAccessPoints::route('/'),
            'create' => CreateWifiAccessPoint::route('/create'),
            'edit' => EditWifiAccessPoint::route('/{record}/edit'),
        ];
    }
}