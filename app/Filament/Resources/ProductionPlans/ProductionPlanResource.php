<?php

namespace App\Filament\Resources\ProductionPlans;

use App\Filament\Resources\ProductionPlans\Pages;
use App\Models\ProductionPlan;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use App\Filament\Concerns\HasPermissionAccess;
use Filament\Support\Icons\Heroicon;

class ProductionPlanResource extends Resource
{
    use HasPermissionAccess;

    protected static ?string $model = ProductionPlan::class;

    public static function getNavigationGroup(): string|null
    {
        return '🏭 Manufaktur';
    }

    protected static ?string $label = 'Rencana Produksi';

    protected static ?string $pluralLabel = 'Rencana Produksi';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static ?int $navigationSort = 7;

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return Schemas\ProductionPlanForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return Tables\ProductionPlanTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProductionPlans::route('/'),
            'create' => Pages\CreateProductionPlan::route('/create'),
            'edit' => Pages\EditProductionPlan::route('/{record}/edit'),
        ];
    }
}
