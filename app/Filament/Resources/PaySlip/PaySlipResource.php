<?php

namespace App\Filament\Resources\PaySlip;

use App\Filament\Resources\PaySlip\Pages\CreatePaySlip;
use App\Filament\Resources\PaySlip\Pages\EditPaySlip;
use App\Filament\Resources\PaySlip\Pages\ListPaySlips;
use App\Filament\Resources\PaySlip\Schemas\PaySlipForm;
use App\Filament\Resources\PaySlip\Tables\PaySlipsTable;
use App\Models\PaySlip;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Panel;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;


use App\Filament\Concerns\HasPermissionAccess;
class PaySlipResource extends Resource
{
    use HasPermissionAccess;
    protected static ?string $model = PaySlip::class;

    public static function getSlug(?Panel $panel = null): string
    {
        return 'pay-slips';
    }

    public static function getNavigationGroup(): string|null
    {
        return 'Payroll';
    }

    protected static ?string $label = 'Slip Gaji';

    protected static ?string $pluralLabel = 'Slip Gaji';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentArrowDown;

    protected static ?int $navigationSort = 206;

    protected static ?string $recordTitleAttribute = 'slip_number';

    public static function form(Schema $schema): Schema
    {
        return PaySlipForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PaySlipsTable::configure($table);
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
            'index' => ListPaySlips::route('/'),
            'create' => CreatePaySlip::route('/create'),
            'edit' => EditPaySlip::route('/{record}/edit'),
        ];
    }
}