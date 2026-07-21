<?php

namespace App\Filament\Resources\VehicleMaintenanceLogResource;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use App\Filament\Concerns\HasPermissionAccess;
use App\Filament\Resources\VehicleMaintenanceLogResource\Pages\ListVehicleMaintenanceLogs;
use App\Filament\Resources\VehicleMaintenanceLogResource\Pages\CreateVehicleMaintenanceLog;
use App\Filament\Resources\VehicleMaintenanceLogResource\Pages\EditVehicleMaintenanceLog;
use App\Filament\Resources\VehicleMaintenanceLogResource\Schemas\VehicleMaintenanceLogForm;
use App\Filament\Resources\VehicleMaintenanceLogResource\Tables\VehicleMaintenanceLogTable;
use App\Models\VehicleMaintenanceLog;

class VehicleMaintenanceLogResource extends Resource
{
    use HasPermissionAccess;
    protected static ?string $model = VehicleMaintenanceLog::class;
    public static function getNavigationGroup(): string|null { return 'Master Data'; }
    protected static ?string $label = 'Log Perawatan';
    protected static ?string $pluralLabel = 'Log Perawatan';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedWrenchScrewdriver;
    protected static ?int $navigationSort = 10;
    protected static ?string $recordTitleAttribute = 'description';
    public static function form(Schema $schema): Schema { return VehicleMaintenanceLogForm::configure($schema); }
    public static function table(Table $table): Table { return VehicleMaintenanceLogTable::configure($table); }
    public static function getRelations(): array { return []; }
    public static function getPages(): array { return [
        'index' => ListVehicleMaintenanceLogs::route('/'),
        'create' => CreateVehicleMaintenanceLog::route('/create'),
        'edit' => EditVehicleMaintenanceLog::route('/{record}/edit'),
    ];}
}
