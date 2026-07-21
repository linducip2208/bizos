<?php

namespace App\Filament\Resources\PayrollApproval;

use App\Filament\Resources\PayrollApproval\Pages\CreatePayrollApproval;
use App\Filament\Resources\PayrollApproval\Pages\EditPayrollApproval;
use App\Filament\Resources\PayrollApproval\Pages\ListPayrollApprovals;
use App\Filament\Resources\PayrollApproval\Schemas\PayrollApprovalForm;
use App\Filament\Resources\PayrollApproval\Tables\PayrollApprovalsTable;
use App\Models\PayrollApproval;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Panel;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

use App\Filament\Concerns\HasPermissionAccess;

class PayrollApprovalResource extends Resource
{
    use HasPermissionAccess;
    protected static ?string $model = PayrollApproval::class;

    public static function getSlug(?Panel $panel = null): string
    {
        return 'payroll-approvals';
    }

    public static function getNavigationGroup(): string|null
    {
        return '💰 Payroll';
    }

    protected static ?string $label = 'Persetujuan Gaji';

    protected static ?string $pluralLabel = 'Persetujuan Gaji';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCheckBadge;

    protected static ?int $navigationSort = 213;

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return PayrollApprovalForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PayrollApprovalsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPayrollApprovals::route('/'),
            'create' => CreatePayrollApproval::route('/create'),
            'edit' => EditPayrollApproval::route('/{record}/edit'),
        ];
    }
}
