<?php

namespace App\Filament\Resources\PosRefunds;

use App\Filament\Resources\PosRefunds\Pages\CreatePosRefund;
use App\Filament\Resources\PosRefunds\Pages\EditPosRefund;
use App\Filament\Resources\PosRefunds\Pages\ListPosRefunds;
use App\Filament\Resources\PosRefunds\Schemas\PosRefundForm;
use App\Filament\Resources\PosRefunds\Tables\PosRefundsTable;
use App\Models\PosRefund;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;


use App\Filament\Concerns\HasPermissionAccess;
class PosRefundResource extends Resource
{
    use HasPermissionAccess;
    protected static ?string $model = PosRefund::class;

    public static function getNavigationGroup(): string|null
    {
        return '🛒 POS & Retail';
    }

    protected static ?string $label = 'Refund POS';

    protected static ?string $pluralLabel = 'Refund POS';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowUturnLeft;

    protected static ?int $navigationSort = 611;

    protected static ?string $recordTitleAttribute = 'refund_number';

    public static function form(Schema $schema): Schema
    {
        return PosRefundForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PosRefundsTable::configure($table);
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
            'index' => ListPosRefunds::route('/'),
            'create' => CreatePosRefund::route('/create'),
            'edit' => EditPosRefund::route('/{record}/edit'),
        ];
    }
}