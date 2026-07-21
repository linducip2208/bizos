<?php

namespace App\Filament\Resources\PayrollPeriod;

use App\Filament\Resources\PayrollPeriod\Pages\CreatePayrollPeriod;
use App\Filament\Resources\PayrollPeriod\Pages\EditPayrollPeriod;
use App\Filament\Resources\PayrollPeriod\Pages\ListPayrollPeriods;
use App\Filament\Resources\PayrollPeriod\Schemas\PayrollPeriodForm;
use App\Filament\Resources\PayrollPeriod\Tables\PayrollPeriodsTable;
use App\Models\PayrollPeriod;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Panel;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;


use App\Filament\Concerns\HasPermissionAccess;
class PayrollPeriodResource extends Resource
{
    use HasPermissionAccess;
    protected static ?string $model = PayrollPeriod::class;

    public static function getSlug(?Panel $panel = null): string
    {
        return 'payroll-periods';
    }

    public static function getNavigationGroup(): string|null
    {
        return 'Payroll';
    }

    protected static ?string $label = 'Periode Gaji';

    protected static ?string $pluralLabel = 'Periode Gaji';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendar;

    protected static ?int $navigationSort = 202;

    protected static ?string $recordTitleAttribute = 'period_code';

    public static function form(Schema $schema): Schema
    {
        return PayrollPeriodForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PayrollPeriodsTable::configure($table);
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
            'index' => ListPayrollPeriods::route('/'),
            'create' => CreatePayrollPeriod::route('/create'),
            'edit' => EditPayrollPeriod::route('/{record}/edit'),
        ];
    }
}
