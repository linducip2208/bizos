<?php

namespace App\Filament\Resources\BillOfMaterials;

use App\Filament\Resources\BillOfMaterials\Pages;
use App\Models\BillOfMaterial;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use App\Filament\Concerns\HasPermissionAccess;
use Filament\Support\Icons\Heroicon;

class BillOfMaterialResource extends Resource
{
    use HasPermissionAccess;

    protected static ?string $model = BillOfMaterial::class;

    public static function getNavigationGroup(): string|null
    {
        return '🏭 Industry';
    }

    protected static ?string $label = 'Bill of Material';

    protected static ?string $pluralLabel = 'Bill of Material';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquaresPlus;

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return \App\Filament\Resources\BillOfMaterials\Schemas\BillOfMaterialForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return \App\Filament\Resources\BillOfMaterials\Tables\BillOfMaterialTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBillOfMaterials::route('/'),
            'create' => Pages\CreateBillOfMaterial::route('/create'),
            'edit' => Pages\EditBillOfMaterial::route('/{record}/edit'),
        ];
    }
}