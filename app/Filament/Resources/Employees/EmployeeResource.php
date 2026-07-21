<?php

namespace App\Filament\Resources\Employees;

use App\Filament\Resources\Employees\Pages\CreateEmployee;
use App\Filament\Resources\Employees\Pages\EditEmployee;
use App\Filament\Resources\Employees\Pages\ListEmployees;
use App\Filament\Resources\Employees\RelationManagers\AttendancesRelationManager;
use App\Filament\Resources\Employees\RelationManagers\EmployeeDocumentsRelationManager;
use App\Filament\Resources\Employees\RelationManagers\FamilyMembersRelationManager;
use App\Filament\Resources\Employees\RelationManagers\LeavesRelationManager;
use App\Filament\Resources\Employees\RelationManagers\OffboardingProgressRelationManager;
use App\Filament\Resources\Employees\RelationManagers\OnboardingProgressRelationManager;
use App\Filament\Resources\Employees\RelationManagers\OvertimesRelationManager;
use App\Filament\Resources\Employees\RelationManagers\ReimbursementsRelationManager;
use App\Filament\Resources\Employees\Schemas\EmployeeForm;
use App\Filament\Resources\Employees\Tables\EmployeesTable;
use App\Models\Employee;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;


use App\Filament\Concerns\HasPermissionAccess;
class EmployeeResource extends Resource
{
    use HasPermissionAccess;
    protected static ?string $model = Employee::class;

    public static function getNavigationGroup(): string|null
    {
        return 'Master Data';
    }

    protected static ?string $label = 'Karyawan';

    protected static ?string $pluralLabel = 'Karyawan';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static ?int $navigationSort = 7;

    protected static ?string $recordTitleAttribute = 'first_name';

    public static function form(Schema $schema): Schema
    {
        return EmployeeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EmployeesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            AttendancesRelationManager::class,
            LeavesRelationManager::class,
            OvertimesRelationManager::class,
            ReimbursementsRelationManager::class,
            FamilyMembersRelationManager::class,
            EmployeeDocumentsRelationManager::class,
            OnboardingProgressRelationManager::class,
            OffboardingProgressRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEmployees::route('/'),
            'create' => CreateEmployee::route('/create'),
            'edit' => EditEmployee::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
