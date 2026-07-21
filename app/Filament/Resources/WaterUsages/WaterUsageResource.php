<?php

namespace App\Filament\Resources\WaterUsages;

use App\Filament\Concerns\HasPermissionAccess;
use App\Filament\Resources\WaterUsages\Pages\CreateWaterUsage;
use App\Filament\Resources\WaterUsages\Pages\EditWaterUsage;
use App\Filament\Resources\WaterUsages\Pages\ListWaterUsages;
use App\Filament\Resources\WaterUsages\Schemas\WaterUsageForm;
use App\Filament\Resources\WaterUsages\Tables\WaterUsagesTable;
use App\Models\WaterUsage;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class WaterUsageResource extends Resource
{
    use HasPermissionAccess;

    protected static ?string $model = WaterUsage::class;

    public static function getNavigationGroup(): string|null
    {
        return '🌱 ESG & Sustainability';
    }

    protected static ?string $label = 'Air';

    protected static ?string $pluralLabel = 'Pemakaian Air';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBeaker;

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return WaterUsageForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WaterUsagesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWaterUsages::route('/'),
            'create' => CreateWaterUsage::route('/create'),
            'edit' => EditWaterUsage::route('/{record}/edit'),
        ];
    }
}