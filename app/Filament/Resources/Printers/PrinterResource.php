<?php

namespace App\Filament\Resources\Printers;

use App\Filament\Concerns\HasPermissionAccess;
use App\Filament\Resources\Printers\Pages\CreatePrinter;
use App\Filament\Resources\Printers\Pages\EditPrinter;
use App\Filament\Resources\Printers\Pages\ListPrinters;
use App\Filament\Resources\Printers\Schemas\PrinterForm;
use App\Filament\Resources\Printers\Tables\PrinterTable;
use App\Models\Printer;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PrinterResource extends Resource
{
    use HasPermissionAccess;

    protected static ?string $model = Printer::class;

    public static function getNavigationGroup(): string|null
    {
        return 'POS & Retail';
    }

    protected static ?string $label = 'Printers';

    protected static ?string $pluralLabel = 'Printers';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHashtag;

    protected static ?int $navigationSort = 606;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return PrinterForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PrinterTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPrinters::route('/'),
            'create' => CreatePrinter::route('/create'),
            'edit' => EditPrinter::route('/{record}/edit'),
        ];
    }
}
