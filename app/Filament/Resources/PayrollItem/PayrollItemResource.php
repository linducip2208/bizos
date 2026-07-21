<?php

namespace App\Filament\Resources\PayrollItem;

use App\Filament\Resources\PayrollItem\Pages\CreatePayrollItem;
use App\Filament\Resources\PayrollItem\Pages\EditPayrollItem;
use App\Filament\Resources\PayrollItem\Pages\ListPayrollItems;
use App\Filament\Resources\PayrollItem\Schemas\PayrollItemForm;
use App\Filament\Resources\PayrollItem\Tables\PayrollItemsTable;
use App\Models\PayrollItem;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Panel;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;


use App\Filament\Concerns\HasPermissionAccess;
class PayrollItemResource extends Resource
{
    use HasPermissionAccess;
    protected static ?string $model = PayrollItem::class;

    public static function getSlug(?Panel $panel = null): string
    {
        return 'payroll-items';
    }

    public static function getNavigationGroup(): string|null
    {
        return '?? HR & Payroll';
    }

    protected static ?string $label = 'Item Penggajian';

    protected static ?string $pluralLabel = 'Item Penggajian';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQueueList;

    protected static ?int $navigationSort = 205;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return PayrollItemForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PayrollItemsTable::configure($table);
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
            'index' => ListPayrollItems::route('/'),
            'create' => CreatePayrollItem::route('/create'),
            'edit' => EditPayrollItem::route('/{record}/edit'),
        ];
    }
}