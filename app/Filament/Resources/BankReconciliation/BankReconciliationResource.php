<?php

namespace App\Filament\Resources\BankReconciliation;

use App\Filament\Resources\BankReconciliation\Pages\CreateBankReconciliation;
use App\Filament\Resources\BankReconciliation\Pages\EditBankReconciliation;
use App\Filament\Resources\BankReconciliation\Pages\ListBankReconciliations;
use App\Filament\Resources\BankReconciliation\Schemas\BankReconciliationForm;
use App\Filament\Resources\BankReconciliation\Tables\BankReconciliationsTable;
use App\Models\BankReconciliation;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

use App\Filament\Concerns\HasPermissionAccess;
use Filament\Panel;

class BankReconciliationResource extends Resource
{
    use HasPermissionAccess;

    protected static ?string $model = BankReconciliation::class;

    public static function getSlug(?Panel $panel = null): string
    {
        return 'bank-reconciliations';
    }

    public static function getNavigationGroup(): string|null
    {
        return '💵 Finance & Accounting';
    }

    protected static ?string $label = 'Rekonsiliasi Bank';

    protected static ?string $pluralLabel = 'Rekonsiliasi Bank';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedScale;

    protected static ?int $navigationSort = 324;

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return BankReconciliationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BankReconciliationsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBankReconciliations::route('/'),
            'create' => CreateBankReconciliation::route('/create'),
            'edit' => EditBankReconciliation::route('/{record}/edit'),
        ];
    }
}