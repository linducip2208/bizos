<?php

namespace App\Filament\Resources\AttendanceConfigs;

use App\Filament\Resources\AttendanceConfigs\Pages\CreateAttendanceConfig;
use App\Filament\Resources\AttendanceConfigs\Pages\EditAttendanceConfig;
use App\Filament\Resources\AttendanceConfigs\Pages\ListAttendanceConfigs;
use App\Filament\Resources\AttendanceConfigs\Schemas\AttendanceConfigForm;
use App\Filament\Resources\AttendanceConfigs\Tables\AttendanceConfigsTable;
use App\Models\AttendanceConfig;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;


use App\Filament\Concerns\HasPermissionAccess;
class AttendanceConfigResource extends Resource
{
    use HasPermissionAccess;
    protected static ?string $model = AttendanceConfig::class;

    public static function getNavigationGroup(): string|null
    {
        return '👥 Human Capital';
    }

    protected static ?string $label = 'Konfigurasi Absensi';

    protected static ?string $pluralLabel = 'Konfigurasi Absensi';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?int $navigationSort = 108;

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return AttendanceConfigForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AttendanceConfigsTable::configure($table);
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
            'index' => ListAttendanceConfigs::route('/'),
            'create' => CreateAttendanceConfig::route('/create'),
            'edit' => EditAttendanceConfig::route('/{record}/edit'),
        ];
    }
}