<?php

namespace App\Filament\Resources\SubscriptionPayments;

use App\Filament\Resources\SubscriptionPayments\Pages\CreateSubscriptionPayment;
use App\Filament\Resources\SubscriptionPayments\Pages\EditSubscriptionPayment;
use App\Filament\Resources\SubscriptionPayments\Pages\ListSubscriptionPayments;
use App\Filament\Resources\SubscriptionPayments\Schemas\SubscriptionPaymentForm;
use App\Filament\Resources\SubscriptionPayments\Tables\SubscriptionPaymentsTable;
use App\Models\SubscriptionPayment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use App\Filament\Concerns\HasPermissionAccess;

class SubscriptionPaymentResource extends Resource
{
    use HasPermissionAccess;

    protected static ?string $model = SubscriptionPayment::class;

    public static function getNavigationGroup(): string|null
    {
        return 'Core';
    }

    protected static ?string $label = 'Pembayaran Langganan';

    protected static ?string $pluralLabel = 'Pembayaran Langganan';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static ?int $navigationSort = 1011;

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return SubscriptionPaymentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SubscriptionPaymentsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSubscriptionPayments::route('/'),
            'create' => CreateSubscriptionPayment::route('/create'),
            'edit' => EditSubscriptionPayment::route('/{record}/edit'),
        ];
    }
}