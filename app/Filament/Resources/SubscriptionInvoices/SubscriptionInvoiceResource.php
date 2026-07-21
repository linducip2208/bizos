<?php

namespace App\Filament\Resources\SubscriptionInvoices;

use App\Filament\Resources\SubscriptionInvoices\Pages\CreateSubscriptionInvoice;
use App\Filament\Resources\SubscriptionInvoices\Pages\EditSubscriptionInvoice;
use App\Filament\Resources\SubscriptionInvoices\Pages\ListSubscriptionInvoices;
use App\Filament\Resources\SubscriptionInvoices\Schemas\SubscriptionInvoiceForm;
use App\Filament\Resources\SubscriptionInvoices\Tables\SubscriptionInvoicesTable;
use App\Models\SubscriptionInvoice;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use App\Filament\Concerns\HasPermissionAccess;

class SubscriptionInvoiceResource extends Resource
{
    use HasPermissionAccess;

    protected static ?string $model = SubscriptionInvoice::class;

    public static function getNavigationGroup(): string|null
    {
        return '?? Sistem';
    }

    protected static ?string $label = 'Invoice Langganan';

    protected static ?string $pluralLabel = 'Invoice Langganan';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentCurrencyDollar;

    protected static ?int $navigationSort = 1010;

    protected static ?string $recordTitleAttribute = 'invoice_number';

    public static function form(Schema $schema): Schema
    {
        return SubscriptionInvoiceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SubscriptionInvoicesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSubscriptionInvoices::route('/'),
            'create' => CreateSubscriptionInvoice::route('/create'),
            'edit' => EditSubscriptionInvoice::route('/{record}/edit'),
        ];
    }
}