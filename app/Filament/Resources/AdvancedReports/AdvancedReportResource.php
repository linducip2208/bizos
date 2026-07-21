<?php

namespace App\Filament\Resources\AdvancedReports;

use App\Filament\Concerns\HasPermissionAccess;
use App\Filament\Resources\AdvancedReports\Pages\AdvancedReportBuilder;
use App\Filament\Resources\AdvancedReports\Pages\ListAdvancedReports;
use App\Filament\Resources\AdvancedReports\Pages\ViewAdvancedReport;
use App\Filament\Resources\AdvancedReports\Tables\AdvancedReportsTable;
use App\Models\AdvancedReport;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AdvancedReportResource extends Resource
{
    use HasPermissionAccess;

    protected static ?string $model = AdvancedReport::class;

    protected static ?string $label = 'Advanced Report';

    protected static ?string $pluralLabel = 'Advanced Report';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPresentationChartLine;

    protected static ?int $navigationSort = 1113;

    public static function getNavigationGroup(): string|null
    {
        return 'Laporan';
    }

    public static function table(Table $table): Table
    {
        return AdvancedReportsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAdvancedReports::route('/'),
            'view' => ViewAdvancedReport::route('/{record}'),
            'builder' => AdvancedReportBuilder::route('/builder'),
            'build' => AdvancedReportBuilder::route('/builder/{record?}'),
        ];
    }
}