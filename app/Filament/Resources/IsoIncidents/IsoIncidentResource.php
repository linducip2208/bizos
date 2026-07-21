<?php

namespace App\Filament\Resources\IsoIncidents;

use App\Filament\Concerns\HasPermissionAccess;
use App\Filament\Resources\IsoIncidents\Pages\CreateIsoIncident;
use App\Filament\Resources\IsoIncidents\Pages\EditIsoIncident;
use App\Filament\Resources\IsoIncidents\Pages\ListIsoIncidents;
use App\Filament\Resources\IsoIncidents\Schemas\IsoIncidentForm;
use App\Filament\Resources\IsoIncidents\Tables\IsoIncidentTable;
use App\Models\IsoIncident;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class IsoIncidentResource extends Resource
{
    protected static ?string $model = IsoIncident::class;

    protected static ?string $label = 'Insiden ISO';

    protected static ?string $pluralLabel = 'Insiden ISO';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBugAnt;

    protected static ?int $navigationSort = 5;

    public static function getNavigationGroup(): string|null
    {
        return '??? Compliance';
    }

    public static function getPages(): array
    {
        return [
            'index' => ListIsoIncidents::route('/'),
            'create' => CreateIsoIncident::route('/create'),
            'edit' => EditIsoIncident::route('/{record}/edit'),
        ];
    }

    public static function form(Schema $schema): Schema
    {
        return IsoIncidentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return IsoIncidentTable::configure($table);
    }
}