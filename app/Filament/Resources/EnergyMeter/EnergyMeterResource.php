<?php

namespace App\Filament\Resources\EnergyMeter;

use App\Filament\Resources\EnergyMeter\Pages\CreateEnergyMeter;
use App\Filament\Resources\EnergyMeter\Pages\EditEnergyMeter;
use App\Filament\Resources\EnergyMeter\Pages\ListEnergyMeters;
use App\Filament\Resources\EnergyMeter\Schemas\EnergyMeterForm;
use App\Filament\Resources\EnergyMeter\Tables\EnergyMetersTable;
use App\Filament\Concerns\HasPermissionAccess;
use App\Models\EnergyMeter;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Panel;

class EnergyMeterResource extends Resource
{
    use HasPermissionAccess;

    protected static ?string $model = EnergyMeter::class;

    public static function getSlug(?Panel $panel = null): string
    {
        return 'energy-meters';
    }

    public static function getNavigationGroup(): string|null
    {
        return '🌱 ESG & Sustainability';
    }

    protected static ?string $label = 'Meter Energi';

    protected static ?string $pluralLabel = 'Meter Energi';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBolt;

    protected static ?int $navigationSort = 1604;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return EnergyMeterForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EnergyMetersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEnergyMeters::route('/'),
            'create' => CreateEnergyMeter::route('/create'),
            'edit' => EditEnergyMeter::route('/{record}/edit'),
        ];
    }
}