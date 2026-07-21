<?php

namespace App\Filament\Resources\Supplier;

use App\Filament\Concerns\HasPermissionAccess;
use App\Filament\Resources\Supplier\Pages\CreateSupplier;
use App\Filament\Resources\Supplier\Pages\EditSupplier;
use App\Filament\Resources\Supplier\Pages\ListSuppliers;
use App\Filament\Resources\Supplier\Schemas\SupplierForm;
use App\Filament\Resources\Supplier\Tables\SupplierTable;
use App\Models\Supplier;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SupplierResource extends Resource
{
    use HasPermissionAccess;

    protected static ?string $model = Supplier::class;

    public static function getNavigationGroup(): string|null
    {
        return 'Procurement & Inventory';
    }

    protected static ?string $label = 'Supplier';

    protected static ?string $pluralLabel = 'Supplier';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTruck;

    protected static ?int $navigationSort = 102;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return SupplierForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SupplierTable::configure($table);
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
            'index' => ListSuppliers::route('/'),
            'create' => CreateSupplier::route('/create'),
            'edit' => EditSupplier::route('/{record}/edit'),
        ];
    }
}
