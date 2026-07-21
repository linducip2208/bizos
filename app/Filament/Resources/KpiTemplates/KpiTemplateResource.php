<?php

namespace App\Filament\Resources\KpiTemplates;

use App\Filament\Concerns\HasPermissionAccess;
use App\Filament\Resources\KpiTemplates\Pages\CreateKpiTemplate;
use App\Filament\Resources\KpiTemplates\Pages\EditKpiTemplate;
use App\Filament\Resources\KpiTemplates\Pages\ListKpiTemplates;
use App\Filament\Resources\KpiTemplates\Schemas\KpiTemplateForm;
use App\Filament\Resources\KpiTemplates\Tables\KpiTemplatesTable;
use App\Models\KpiTemplate;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class KpiTemplateResource extends Resource
{
    use HasPermissionAccess;
    protected static ?string $model = KpiTemplate::class;

    public static function getNavigationGroup(): string|null
    {
        return 'HRM';
    }

    protected static ?string $label = 'Template KPI';
    protected static ?string $pluralLabel = 'Template KPI';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPresentationChartBar;
    protected static ?int $navigationSort = 126;
    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return KpiTemplateForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return KpiTemplatesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListKpiTemplates::route('/'),
            'create' => CreateKpiTemplate::route('/create'),
            'edit' => EditKpiTemplate::route('/{record}/edit'),
        ];
    }
}
