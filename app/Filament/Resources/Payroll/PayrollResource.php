<?php

namespace App\Filament\Resources\Payroll;

use App\Filament\Resources\Payroll\Pages\CreatePayroll;
use App\Filament\Resources\Payroll\Pages\EditPayroll;
use App\Filament\Resources\Payroll\Pages\ListPayrolls;
use App\Filament\Resources\Payroll\Schemas\PayrollForm;
use App\Filament\Resources\Payroll\Tables\PayrollsTable;
use App\Models\Payroll as PayrollModel;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Panel;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;


use App\Filament\Concerns\HasPermissionAccess;
class PayrollResource extends Resource
{
    use HasPermissionAccess;
    protected static ?string $model = PayrollModel::class;

    public static function getSlug(?Panel $panel = null): string
    {
        return 'payrolls';
    }

    public static function getNavigationGroup(): string|null
    {
        return '?? HR & Payroll';
    }

    protected static ?string $label = 'Penggajian';

    protected static ?string $pluralLabel = 'Penggajian';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static ?int $navigationSort = 204;

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return PayrollForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PayrollsTable::configure($table);
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
            'index' => ListPayrolls::route('/'),
            'create' => CreatePayroll::route('/create'),
            'edit' => EditPayroll::route('/{record}/edit'),
        ];
    }
}