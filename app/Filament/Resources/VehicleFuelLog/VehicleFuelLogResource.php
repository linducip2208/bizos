<?php

namespace App\Filament\Resources\VehicleFuelLog;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use App\Filament\Concerns\HasPermissionAccess;
use App\Filament\Resources\VehicleFuelLog\Pages\ListVehicleFuelLogs;
use App\Filament\Resources\VehicleFuelLog\Pages\CreateVehicleFuelLog;
use App\Filament\Resources\VehicleFuelLog\Pages\EditVehicleFuelLog;
use App\Filament\Resources\VehicleFuelLog\Schemas\VehicleFuelLogForm;
use App\Filament\Resources\VehicleFuelLog\Tables\VehicleFuelLogTable;
use App\Models\VehicleFuelLog;

class VehicleFuelLogResource extends Resource
{
    use HasPermissionAccess;

    protected static ?string $model = VehicleFuelLog::class;

    public static function getNavigationGroup(): string|null { return '🏢 Organisasi'; }

    protected static ?string $label = 'Log BBM';

    protected static ?string $pluralLabel = 'Log BBM';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBeaker;

    protected static ?int $navigationSort = 9;

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema { return VehicleFuelLogForm::configure($schema); }

    public static function table(Table $table): Table { return VehicleFuelLogTable::configure($table); }

    public static function getRelations(): array { return []; }

    public static function getPages(): array
    {
        return [
            'index' => ListVehicleFuelLogs::route('/'),
            'create' => CreateVehicleFuelLog::route('/create'),
            'edit' => EditVehicleFuelLog::route('/{record}/edit'),
        ];
    }
}