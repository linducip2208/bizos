<?php

namespace App\Filament\Resources\PosVouchers;

use App\Filament\Resources\PosVouchers\Pages\CreatePosVoucher;
use App\Filament\Resources\PosVouchers\Pages\EditPosVoucher;
use App\Filament\Resources\PosVouchers\Pages\ListPosVouchers;
use App\Filament\Resources\PosVouchers\Schemas\PosVoucherForm;
use App\Filament\Resources\PosVouchers\Tables\PosVoucherTable;
use App\Models\PosVoucher;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;


use App\Filament\Concerns\HasPermissionAccess;
class PosVoucherResource extends Resource
{
    use HasPermissionAccess;
    protected static ?string $model = PosVoucher::class;

    public static function getNavigationGroup(): string|null
    {
        return '🛒 POS & Retail';
    }

    protected static ?string $label = 'Voucher';

    protected static ?string $pluralLabel = 'Voucher';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTicket;

    protected static ?int $navigationSort = 604;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return PosVoucherForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PosVoucherTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPosVouchers::route('/'),
            'create' => CreatePosVoucher::route('/create'),
            'edit' => EditPosVoucher::route('/{record}/edit'),
        ];
    }
}