<?php

namespace App\Filament\Resources\EnergyReadingResource;

use App\Filament\Resources\EnergyReadingResource\Pages\ListEnergyReadings;
use App\Filament\Resources\EnergyReadingResource\Tables\EnergyReadingsTable;
use App\Filament\Concerns\HasPermissionAccess;
use App\Models\EnergyReading;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Panel;

class EnergyReadingResource extends Resource
{
    use HasPermissionAccess;

    protected static ?string $model = EnergyReading::class;

    public static function getSlug(?Panel $panel = null): string
    {
        return 'energy-readings';
    }

    public static function getNavigationGroup(): string|null
    {
        return 'IoT & Sensor';
    }

    protected static ?string $label = 'Pembacaan Energi';

    protected static ?string $pluralLabel = 'Pembacaan Energi';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPresentationChartLine;

    protected static ?int $navigationSort = 1605;

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return $schema;
    }

    public static function table(Table $table): Table
    {
        return EnergyReadingsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEnergyReadings::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
