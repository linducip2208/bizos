<?php

namespace App\Filament\Resources\PosPayments;

use App\Filament\Resources\PosPayments\Pages\CreatePosPayment;
use App\Filament\Resources\PosPayments\Pages\EditPosPayment;
use App\Filament\Resources\PosPayments\Pages\ListPosPayments;
use App\Filament\Resources\PosPayments\Schemas\PosPaymentForm;
use App\Filament\Resources\PosPayments\Tables\PosPaymentsTable;
use App\Models\PosPayment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;


use App\Filament\Concerns\HasPermissionAccess;
class PosPaymentResource extends Resource
{
    protected static bool $shouldRegisterNavigation = false;
    use HasPermissionAccess;
    protected static ?string $model = PosPayment::class;

    public static function getNavigationGroup(): string|null
    {
        return '🛒 POS & Retail';
    }

    protected static ?string $label = 'Pembayaran POS';

    protected static ?string $pluralLabel = 'Pembayaran POS';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static ?int $navigationSort = 610;

    protected static ?string $recordTitleAttribute = 'reference_number';

    public static function form(Schema $schema): Schema
    {
        return PosPaymentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PosPaymentsTable::configure($table);
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
            'index' => ListPosPayments::route('/'),
            'create' => CreatePosPayment::route('/create'),
            'edit' => EditPosPayment::route('/{record}/edit'),
        ];
    }
}