<?php

namespace App\Filament\Resources\CashierShifts;

use App\Filament\Resources\CashierShifts\Pages\CreateCashierShift;
use App\Filament\Resources\CashierShifts\Pages\EditCashierShift;
use App\Filament\Resources\CashierShifts\Pages\ListCashierShifts;
use App\Filament\Resources\CashierShifts\Schemas\CashierShiftForm;
use App\Filament\Resources\CashierShifts\Tables\CashierShiftTable;
use App\Models\CashierShift;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;


use App\Filament\Concerns\HasPermissionAccess;
class CashierShiftResource extends Resource
{
    use HasPermissionAccess;
    protected static ?string $model = CashierShift::class;

    public static function getNavigationGroup(): string|null
    {
        return 'POS';
    }

    protected static ?string $label = 'Shift Kasir';

    protected static ?string $pluralLabel = 'Shift Kasir';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedComputerDesktop;

    protected static ?int $navigationSort = 605;

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return CashierShiftForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CashierShiftTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCashierShifts::route('/'),
            'create' => CreateCashierShift::route('/create'),
            'edit' => EditCashierShift::route('/{record}/edit'),
        ];
    }
}