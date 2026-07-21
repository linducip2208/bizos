<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductionQcCheckResource\Pages;
use App\Models\ProductionQcCheck;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use App\Filament\Concerns\HasPermissionAccess;
use Filament\Support\Icons\Heroicon;

class ProductionQcCheckResource extends Resource
{
    use HasPermissionAccess;

    protected static ?string $model = ProductionQcCheck::class;

    public static function getNavigationGroup(): string|null
    {
        return 'Manufaktur';
    }

    protected static ?string $label = 'QC Check';

    protected static ?string $pluralLabel = 'QC Check';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCheckCircle;

    protected static ?int $navigationSort = 6;

    protected static ?string $recordTitleAttribute = 'parameter';

    public static function form(Schema $schema): Schema
    {
        return \App\Filament\Resources\ProductionQcCheckResource\Schemas\ProductionQcCheckForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return \App\Filament\Resources\ProductionQcCheckResource\Tables\ProductionQcCheckTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProductionQcChecks::route('/'),
            'create' => Pages\CreateProductionQcCheck::route('/create'),
            'edit' => Pages\EditProductionQcCheck::route('/{record}/edit'),
        ];
    }
}
