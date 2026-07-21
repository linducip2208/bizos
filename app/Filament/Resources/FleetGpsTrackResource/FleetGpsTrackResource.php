<?php

namespace App\Filament\Resources\FleetGpsTrackResource;

use App\Filament\Concerns\HasPermissionAccess;
use App\Filament\Resources\FleetGpsTrackResource\Pages\CreateFleetGpsTrack;
use App\Filament\Resources\FleetGpsTrackResource\Pages\EditFleetGpsTrack;
use App\Filament\Resources\FleetGpsTrackResource\Pages\ListFleetGpsTracks;
use App\Filament\Resources\FleetGpsTrackResource\Schemas\FleetGpsTrackForm;
use App\Filament\Resources\FleetGpsTrackResource\Tables\FleetGpsTrackTable;
use App\Models\FleetGpsTrack;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class FleetGpsTrackResource extends Resource
{
    use HasPermissionAccess;

    protected static ?string $model = FleetGpsTrack::class;

    public static function getNavigationGroup(): string|null
    {
        return 'Logistik';
    }

    protected static ?string $label = 'GPS Tracking';

    protected static ?string $pluralLabel = 'GPS Tracking';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMapPin;

    protected static ?int $navigationSort = 4;

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return FleetGpsTrackForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FleetGpsTrackTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFleetGpsTracks::route('/'),
            'create' => CreateFleetGpsTrack::route('/create'),
            'edit' => EditFleetGpsTrack::route('/{record}/edit'),
        ];
    }
}
