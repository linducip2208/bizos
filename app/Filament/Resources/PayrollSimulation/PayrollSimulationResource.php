<?php

namespace App\Filament\Resources\PayrollSimulation;

use App\Filament\Resources\PayrollSimulation\Pages\CreatePayrollSimulation;
use App\Filament\Resources\PayrollSimulation\Pages\EditPayrollSimulation;
use App\Filament\Resources\PayrollSimulation\Pages\ListPayrollSimulations;
use App\Filament\Resources\PayrollSimulation\Pages\ViewPayrollSimulation;
use App\Filament\Resources\PayrollSimulation\Schemas\PayrollSimulationForm;
use App\Filament\Resources\PayrollSimulation\Tables\PayrollSimulationsTable;
use App\Models\PayrollSimulation;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Panel;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

use App\Filament\Concerns\HasPermissionAccess;

class PayrollSimulationResource extends Resource
{
    use HasPermissionAccess;
    protected static ?string $model = PayrollSimulation::class;

    public static function getNavigationGroup(): string|null
    {
        return \App\Filament\Navigation\NavigationGroup::PAYROLL->value;
    }

    protected static ?string $label = 'Payroll Simulation';

    protected static ?string $pluralLabel = 'Simulasi Gaji';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalculator;

    protected static ?int $navigationSort = 214;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return PayrollSimulationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PayrollSimulationsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPayrollSimulations::route('/'),
            'create' => CreatePayrollSimulation::route('/create'),
            'edit' => EditPayrollSimulation::route('/{record}/edit'),
            'view' => ViewPayrollSimulation::route('/{record}'),
        ];
    }
}
