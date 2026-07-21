<?php

namespace App\Filament\Resources\FieldService\VanInventoryResource;

use App\Filament\Resources\FieldService\VanInventoryResource\Pages\ListVanInventories;
use App\Filament\Resources\FieldService\VanInventoryResource\Pages\CreateVanInventory;
use App\Filament\Resources\FieldService\VanInventoryResource\Pages\EditVanInventory;
use App\Filament\Resources\FieldService\VanInventoryResource\Schemas\VanInventoryForm;
use App\Filament\Resources\FieldService\VanInventoryResource\Tables\VanInventoryTable;
use App\Models\VanInventory;
use App\Filament\Concerns\HasPermissionAccess;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class VanInventoryResource extends Resource
{
    use HasPermissionAccess;

    protected static ?string $model = VanInventory::class;

    public static function getNavigationGroup(): ?string
    {
        return 'Field Service';
    }

    protected static ?string $label = 'Stok Van';

    protected static ?string $pluralLabel = 'Stok Van';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-archive-box';

    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return VanInventoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return VanInventoryTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListVanInventories::route('/'),
            'create' => CreateVanInventory::route('/create'),
            'edit' => EditVanInventory::route('/{record}/edit'),
        ];
    }
}
