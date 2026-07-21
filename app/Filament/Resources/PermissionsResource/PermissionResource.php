<?php

namespace App\Filament\Resources\PermissionsResource;

use App\Filament\Resources\PermissionsResource\Pages\CreatePermission;
use App\Filament\Resources\PermissionsResource\Pages\EditPermission;
use App\Filament\Resources\PermissionsResource\Pages\ListPermissions;
use App\Filament\Resources\PermissionsResource\Schemas\PermissionForm;
use App\Filament\Resources\PermissionsResource\Tables\PermissionsTable;
use App\Models\Permission;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Panel;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;


use App\Filament\Concerns\HasPermissionAccess;
class PermissionResource extends Resource
{
    use HasPermissionAccess;
    protected static ?string $model = Permission::class;

    protected static ?string $label = 'Izin Akses';

    protected static ?string $pluralLabel = 'Izin Akses';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedLockClosed;

    protected static ?int $navigationSort = 1002;

    public static function getSlug(?Panel $panel = null): string
    {
        return 'permissions';
    }

    public static function getNavigationGroup(): string|null
    {
        return 'Sistem';
    }

    public static function form(Schema $schema): Schema
    {
        return PermissionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PermissionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPermissions::route('/'),
            'create' => CreatePermission::route('/create'),
            'edit' => EditPermission::route('/{record}/edit'),
        ];
    }
}
