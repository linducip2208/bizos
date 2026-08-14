<?php

namespace App\Filament\Resources\ReceiptLayouts;

use App\Filament\Concerns\HasPermissionAccess;
use App\Filament\Resources\ReceiptLayouts\Pages\CreateReceiptLayout;
use App\Filament\Resources\ReceiptLayouts\Pages\EditReceiptLayout;
use App\Filament\Resources\ReceiptLayouts\Pages\ListReceiptLayouts;
use App\Filament\Resources\ReceiptLayouts\Schemas\ReceiptLayoutForm;
use App\Filament\Resources\ReceiptLayouts\Tables\ReceiptLayoutTable;
use App\Models\ReceiptLayout;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ReceiptLayoutResource extends Resource
{
    use HasPermissionAccess;

    protected static ?string $model = ReceiptLayout::class;

    public static function getNavigationGroup(): string|null
    {
        return '🛒 POS & Retail';
    }

    protected static ?string $label = 'Layout Struk';

    protected static ?string $pluralLabel = 'Layout Struk';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?int $navigationSort = 607;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return ReceiptLayoutForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ReceiptLayoutTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListReceiptLayouts::route('/'),
            'create' => CreateReceiptLayout::route('/create'),
            'edit' => EditReceiptLayout::route('/{record}/edit'),
        ];
    }
}
