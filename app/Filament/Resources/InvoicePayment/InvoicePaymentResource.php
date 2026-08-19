<?php

namespace App\Filament\Resources\InvoicePayment;

use App\Filament\Resources\InvoicePayment\Pages\CreateInvoicePayment;
use App\Filament\Resources\InvoicePayment\Pages\EditInvoicePayment;
use App\Filament\Resources\InvoicePayment\Pages\ListInvoicePayments;
use App\Filament\Resources\InvoicePayment\Schemas\InvoicePaymentForm;
use App\Filament\Resources\InvoicePayment\Tables\InvoicePaymentsTable;
use App\Models\InvoicePayment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Panel;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;


use App\Filament\Concerns\HasPermissionAccess;
class InvoicePaymentResource extends Resource
{
    protected static bool $shouldRegisterNavigation = false;
    use HasPermissionAccess;
    protected static ?string $model = InvoicePayment::class;

    public static function getNavigationGroup(): string|null
    {
        return \App\Filament\Navigation\NavigationGroup::FINANCE->value;
    }

    protected static ?string $label = 'Invoice Payments';

    protected static ?string $pluralLabel = 'Invoice Payments';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static ?int $navigationSort = 319;

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return InvoicePaymentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InvoicePaymentsTable::configure($table);
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
            'index' => ListInvoicePayments::route('/'),
            'create' => CreateInvoicePayment::route('/create'),
            'edit' => EditInvoicePayment::route('/{record}/edit'),
        ];
    }
}