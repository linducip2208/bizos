<?php

namespace App\Filament\Resources\IotAlert;

use App\Filament\Resources\IotAlert\Pages\EditIotAlert;
use App\Filament\Resources\IotAlert\Pages\ListIotAlerts;
use App\Filament\Resources\IotAlert\Schemas\IotAlertForm;
use App\Filament\Resources\IotAlert\Tables\IotAlertsTable;
use App\Filament\Concerns\HasPermissionAccess;
use App\Models\IotAlert;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Panel;

class IotAlertResource extends Resource
{
    use HasPermissionAccess;

    protected static ?string $model = IotAlert::class;

    public static function getSlug(?Panel $panel = null): string
    {
        return 'iot-alerts';
    }

    public static function getNavigationGroup(): string|null
    {
        return '?? Extras';
    }

    protected static ?string $label = 'Alert Sensor';

    protected static ?string $pluralLabel = 'Alert Sensor';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBellAlert;

    protected static ?int $navigationSort = 1603;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return IotAlertForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return IotAlertsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListIotAlerts::route('/'),
            'edit' => EditIotAlert::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}