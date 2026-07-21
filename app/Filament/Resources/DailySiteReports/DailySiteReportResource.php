<?php

namespace App\Filament\Resources\DailySiteReports;

use App\Filament\Concerns\HasPermissionAccess;
use App\Filament\Resources\DailySiteReports\Pages\CreateDailySiteReport;
use App\Filament\Resources\DailySiteReports\Pages\EditDailySiteReport;
use App\Filament\Resources\DailySiteReports\Pages\ListDailySiteReports;
use App\Filament\Resources\DailySiteReports\Schemas\DailySiteReportForm;
use App\Filament\Resources\DailySiteReports\Tables\DailySiteReportTable;
use App\Models\DailySiteReport;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class DailySiteReportResource extends Resource
{
    use HasPermissionAccess;

    protected static ?string $model = DailySiteReport::class;

    public static function getNavigationGroup(): string|null
    {
        return '🏗️ Konstruksi';
    }

    protected static ?string $label = 'Laporan Harian';
    protected static ?string $pluralLabel = 'Laporan Harian';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static ?int $navigationSort = 604;

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return DailySiteReportForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DailySiteReportTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDailySiteReports::route('/'),
            'create' => CreateDailySiteReport::route('/create'),
            'edit' => EditDailySiteReport::route('/{record}/edit'),
        ];
    }
}