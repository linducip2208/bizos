<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductionOrderResource\Pages;
use App\Models\ProductionOrder;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use App\Filament\Concerns\HasPermissionAccess;
use Filament\Support\Icons\Heroicon;

class ProductionOrderResource extends Resource
{
    use HasPermissionAccess;

    protected static ?string $model = ProductionOrder::class;

    public static function getNavigationGroup(): string|null
    {
        return 'Manufaktur';
    }

    protected static ?string $label = 'Production Order';

    protected static ?string $pluralLabel = 'Production Order';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static ?int $navigationSort = 5;

    protected static ?string $recordTitleAttribute = 'po_number';

    public static function form(Schema $schema): Schema
    {
        return \App\Filament\Resources\ProductionOrderResource\Schemas\ProductionOrderForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return \App\Filament\Resources\ProductionOrderResource\Tables\ProductionOrderTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProductionOrders::route('/'),
            'create' => Pages\CreateProductionOrder::route('/create'),
            'edit' => Pages\EditProductionOrder::route('/{record}/edit'),
        ];
    }
}
