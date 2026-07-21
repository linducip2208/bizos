<?php

namespace App\Filament\Resources\EmploymentTypes;

use App\Filament\Concerns\HasPermissionAccess;
use App\Filament\Resources\EmploymentTypes\Pages\CreateEmploymentType;
use App\Filament\Resources\EmploymentTypes\Pages\EditEmploymentType;
use App\Filament\Resources\EmploymentTypes\Pages\ListEmploymentTypes;
use App\Filament\Resources\EmploymentTypes\Schemas\EmploymentTypeForm;
use App\Filament\Resources\EmploymentTypes\Tables\EmploymentTypesTable;
use App\Models\EmploymentType;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class EmploymentTypeResource extends Resource
{
    use HasPermissionAccess;

    protected static ?string $model = EmploymentType::class;

    public static function getNavigationGroup(): string|null
    {
        return '🏢 Organisasi';
    }

    protected static ?string $label = 'Tipe Karyawan';

    protected static ?string $pluralLabel = 'Tipe Karyawan';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static ?int $navigationSort = 7;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return EmploymentTypeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EmploymentTypesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEmploymentTypes::route('/'),
            'create' => CreateEmploymentType::route('/create'),
            'edit' => EditEmploymentType::route('/{record}/edit'),
        ];
    }
}
