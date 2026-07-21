<?php

namespace App\Filament\Resources\EmployeeSalaryComponent;

use App\Filament\Resources\EmployeeSalaryComponent\Pages\CreateEmployeeSalaryComponent;
use App\Filament\Resources\EmployeeSalaryComponent\Pages\EditEmployeeSalaryComponent;
use App\Filament\Resources\EmployeeSalaryComponent\Pages\ListEmployeeSalaryComponents;
use App\Filament\Resources\EmployeeSalaryComponent\Schemas\EmployeeSalaryComponentForm;
use App\Filament\Resources\EmployeeSalaryComponent\Tables\EmployeeSalaryComponentsTable;
use App\Models\EmployeeSalaryComponent;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Filament\Panel;


use App\Filament\Concerns\HasPermissionAccess;
class EmployeeSalaryComponentResource extends Resource
{
    use HasPermissionAccess;
    protected static ?string $model = EmployeeSalaryComponent::class;

    public static function getSlug(?Panel $panel = null): string
    {
        return 'employee-salary-components';
    }

    public static function getNavigationGroup(): string|null
    {
        return 'Payroll';
    }

    protected static ?string $label = 'Komponen Gaji Karyawan';

    protected static ?string $pluralLabel = 'Komponen Gaji Karyawan';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCurrencyDollar;

    protected static ?int $navigationSort = 203;

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return EmployeeSalaryComponentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EmployeeSalaryComponentsTable::configure($table);
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
            'index' => ListEmployeeSalaryComponents::route('/'),
            'create' => CreateEmployeeSalaryComponent::route('/create'),
            'edit' => EditEmployeeSalaryComponent::route('/{record}/edit'),
        ];
    }
}
