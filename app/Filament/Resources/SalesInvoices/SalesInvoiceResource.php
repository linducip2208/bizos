<?php

namespace App\Filament\Resources\SalesInvoices;

use App\Filament\Concerns\HasPermissionAccess;
use App\Filament\Resources\SalesInvoices\Pages\CreateSalesInvoice;
use App\Filament\Resources\SalesInvoices\Pages\EditSalesInvoice;
use App\Filament\Resources\SalesInvoices\Pages\ListSalesInvoices;
use App\Filament\Resources\SalesInvoices\Pages\ViewSalesInvoice;
use App\Filament\Resources\SalesInvoices\Schemas\SalesInvoiceForm;
use App\Filament\Resources\SalesInvoices\Tables\SalesInvoicesTable;
use App\Models\SalesInvoice;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SalesInvoiceResource extends Resource
{
    use HasPermissionAccess;

    // Gunakan Invoice (Finance) untuk semua billing — SalesInvoice sudah dinormalisasi ke Invoice.
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $model = SalesInvoice::class;

    public static function getNavigationGroup(): string|null
    {
        return '📈 Sales & CRM';
    }

    protected static ?string $label = 'Sales Invoice';

    protected static ?string $pluralLabel = 'Sales Invoice';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentCurrencyDollar;

    protected static ?int $navigationSort = 412;

    protected static ?string $recordTitleAttribute = 'invoice_number';

    public static function form(Schema $schema): Schema
    {
        return SalesInvoiceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SalesInvoicesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSalesInvoices::route('/'),
            'create' => CreateSalesInvoice::route('/create'),
            'edit' => EditSalesInvoice::route('/{record}/edit'),
            'view' => ViewSalesInvoice::route('/{record}'),
        ];
    }
}
