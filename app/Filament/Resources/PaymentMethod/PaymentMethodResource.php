<?php

namespace App\Filament\Resources\PaymentMethod;

use App\Filament\Resources\PaymentMethod\Pages\CreatePaymentMethod;
use App\Filament\Resources\PaymentMethod\Pages\EditPaymentMethod;
use App\Filament\Resources\PaymentMethod\Pages\ListPaymentMethods;
use App\Filament\Resources\PaymentMethod\Schemas\PaymentMethodForm;
use App\Filament\Resources\PaymentMethod\Tables\PaymentMethodsTable;
use App\Models\PaymentMethod;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Panel;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;


use App\Filament\Concerns\HasPermissionAccess;
class PaymentMethodResource extends Resource
{
    use HasPermissionAccess;
    protected static ?string $model = PaymentMethod::class;

    public static function getSlug(?Panel $panel = null): string
    {
        return 'payment-methods';
    }

    public static function getNavigationGroup(): string|null
    {
        return 'Finance';
    }

    protected static ?string $label = 'Metode Pembayaran';

    protected static ?string $pluralLabel = 'Metode Pembayaran';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;

    protected static ?int $navigationSort = 303;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return PaymentMethodForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PaymentMethodsTable::configure($table);
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
            'index' => ListPaymentMethods::route('/'),
            'create' => CreatePaymentMethod::route('/create'),
            'edit' => EditPaymentMethod::route('/{record}/edit'),
        ];
    }
}
