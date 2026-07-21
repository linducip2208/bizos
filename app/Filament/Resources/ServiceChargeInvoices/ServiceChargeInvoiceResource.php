<?php

namespace App\Filament\Resources\ServiceChargeInvoices;

use App\Filament\Concerns\HasPermissionAccess;
use App\Filament\Resources\ServiceChargeInvoices\Pages\CreateServiceChargeInvoice;
use App\Filament\Resources\ServiceChargeInvoices\Pages\EditServiceChargeInvoice;
use App\Filament\Resources\ServiceChargeInvoices\Pages\ListServiceChargeInvoices;
use App\Filament\Resources\ServiceChargeInvoices\Schemas\ServiceChargeInvoiceForm;
use App\Filament\Resources\ServiceChargeInvoices\Tables\ServiceChargeInvoiceTable;
use App\Models\ServiceChargeInvoice;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ServiceChargeInvoiceResource extends Resource
{
    use HasPermissionAccess;

    protected static ?string $model = ServiceChargeInvoice::class;

    public static function getNavigationGroup(): string|null
    {
        return 'Properti';
    }

    protected static ?string $label = 'Tagihan IPL';
    protected static ?string $pluralLabel = 'Tagihan IPL';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentCurrencyDollar;

    protected static ?int $navigationSort = 803;

    protected static ?string $recordTitleAttribute = 'invoice_number';

    public static function form(Schema $schema): Schema
    {
        return ServiceChargeInvoiceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ServiceChargeInvoiceTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListServiceChargeInvoices::route('/'),
            'create' => CreateServiceChargeInvoice::route('/create'),
            'edit' => EditServiceChargeInvoice::route('/{record}/edit'),
        ];
    }
}