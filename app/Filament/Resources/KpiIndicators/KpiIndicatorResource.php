<?php

namespace App\Filament\Resources\KpiIndicators;

use App\Filament\Concerns\HasPermissionAccess;
use App\Filament\Resources\KpiIndicators\Pages\CreateKpiIndicator;
use App\Filament\Resources\KpiIndicators\Pages\EditKpiIndicator;
use App\Filament\Resources\KpiIndicators\Pages\ListKpiIndicators;
use App\Filament\Resources\KpiIndicators\Schemas\KpiIndicatorForm;
use App\Filament\Resources\KpiIndicators\Tables\KpiIndicatorsTable;
use App\Models\KpiIndicator;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class KpiIndicatorResource extends Resource
{
    use HasPermissionAccess;
    protected static ?string $model = KpiIndicator::class;

    public static function getNavigationGroup(): string|null
    {
        return 'HRM';
    }

    protected static ?string $label = 'Indikator KPI';
    protected static ?string $pluralLabel = 'Indikator KPI';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;
    protected static ?int $navigationSort = 127;
    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return KpiIndicatorForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return KpiIndicatorsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListKpiIndicators::route('/'),
            'create' => CreateKpiIndicator::route('/create'),
            'edit' => EditKpiIndicator::route('/{record}/edit'),
        ];
    }
}
