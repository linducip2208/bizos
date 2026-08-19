<?php

namespace App\Filament\Resources\EnergyReading;

use App\Filament\Resources\EnergyReading\Pages\ListEnergyReadings;
use App\Filament\Resources\EnergyReading\Tables\EnergyReadingsTable;
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
    protected static bool $shouldRegisterNavigation = false;
    use HasPermissionAccess;

    protected static ?string $model = EnergyReading::class;

    public static function getSlug(?Panel $panel = null): string
    {
        return 'Compliance & Risk';
    }

    public static function getNavigationGroup(): string|null
    {
        return 'Compliance & Risk';
    }

    protected static ?string $label = 'Energy Readings';

    protected static ?string $pluralLabel = 'Energy Readings';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAtSymbol;

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