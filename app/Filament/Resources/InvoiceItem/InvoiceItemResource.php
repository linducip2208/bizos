<?php

namespace App\Filament\Resources\InvoiceItem;

use App\Filament\Resources\InvoiceItem\Pages\CreateInvoiceItem;
use App\Filament\Resources\InvoiceItem\Pages\EditInvoiceItem;
use App\Filament\Resources\InvoiceItem\Pages\ListInvoiceItems;
use App\Filament\Resources\InvoiceItem\Schemas\InvoiceItemForm;
use App\Filament\Resources\InvoiceItem\Tables\InvoiceItemsTable;
use App\Models\InvoiceItem;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Panel;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;


use App\Filament\Concerns\HasPermissionAccess;
class InvoiceItemResource extends Resource
{
    use HasPermissionAccess;
    protected static ?string $model = InvoiceItem::class;

    public static function getSlug(?Panel $panel = null): string
    {
        return 'invoice-items';
    }

    public static function getNavigationGroup(): string|null
    {
        return 'Finance';
    }

    protected static ?string $label = 'Item Invoice';

    protected static ?string $pluralLabel = 'Item Invoice';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedListBullet;

    protected static ?int $navigationSort = 309;

    protected static ?string $recordTitleAttribute = 'description';

    public static function form(Schema $schema): Schema
    {
        return InvoiceItemForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InvoiceItemsTable::configure($table);
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
            'index' => ListInvoiceItems::route('/'),
            'create' => CreateInvoiceItem::route('/create'),
            'edit' => EditInvoiceItem::route('/{record}/edit'),
        ];
    }
}