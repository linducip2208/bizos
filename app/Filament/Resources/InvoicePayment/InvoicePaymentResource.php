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
    use HasPermissionAccess;
    protected static ?string $model = InvoicePayment::class;

    public static function getSlug(?Panel $panel = null): string
    {
        return 'invoice-payments';
    }

    public static function getNavigationGroup(): string|null
    {
        return 'Finance';
    }

    protected static ?string $label = 'Pembayaran Invoice';

    protected static ?string $pluralLabel = 'Pembayaran Invoice';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowPath;

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