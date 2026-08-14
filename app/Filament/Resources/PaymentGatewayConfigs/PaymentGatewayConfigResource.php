<?php

namespace App\Filament\Resources\PaymentGatewayConfigs;

use App\Filament\Concerns\HasPermissionAccess;
use App\Filament\Resources\PaymentGatewayConfigs\Pages\CreatePaymentGatewayConfig;
use App\Filament\Resources\PaymentGatewayConfigs\Pages\EditPaymentGatewayConfig;
use App\Filament\Resources\PaymentGatewayConfigs\Pages\ListPaymentGatewayConfigs;
use App\Filament\Resources\PaymentGatewayConfigs\Schemas\PaymentGatewayConfigForm;
use App\Filament\Resources\PaymentGatewayConfigs\Tables\PaymentGatewayConfigsTable;
use App\Models\PaymentGatewayConfig;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PaymentGatewayConfigResource extends Resource
{
    use HasPermissionAccess;

    protected static ?string $model = PaymentGatewayConfig::class;

    public static function getNavigationGroup(): string|null
    {
        return '🔗 Integrations';
    }

    protected static ?string $label = 'Payment Gateway';

    protected static ?string $pluralLabel = 'Payment Gateway';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;

    protected static ?int $navigationSort = 9;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return PaymentGatewayConfigForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PaymentGatewayConfigsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPaymentGatewayConfigs::route('/'),
            'create' => CreatePaymentGatewayConfig::route('/create'),
            'edit' => EditPaymentGatewayConfig::route('/{record}/edit'),
        ];
    }
}
