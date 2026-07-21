<?php

namespace App\Filament\Resources\IotReading;

use App\Filament\Resources\IotReading\Pages\ListIotReadings;
use App\Filament\Resources\IotReading\Tables\IotReadingsTable;
use App\Filament\Concerns\HasPermissionAccess;
use App\Models\IotReading;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Panel;

class IotReadingResource extends Resource
{
    use HasPermissionAccess;

    protected static ?string $model = IotReading::class;

    public static function getSlug(?Panel $panel = null): string
    {
        return 'iot-readings';
    }

    public static function getNavigationGroup(): string|null
    {
        return 'IoT & Sensor';
    }

    protected static ?string $label = 'Pembacaan Sensor';

    protected static ?string $pluralLabel = 'Pembacaan Sensor';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static ?int $navigationSort = 1602;

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return $schema;
    }

    public static function table(Table $table): Table
    {
        return IotReadingsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListIotReadings::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}