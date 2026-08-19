<?php

namespace App\Filament\Resources\ReportSchedules;

use App\Filament\Resources\ReportSchedules\Pages\CreateReportSchedule;
use App\Filament\Resources\ReportSchedules\Pages\EditReportSchedule;
use App\Filament\Resources\ReportSchedules\Pages\ListReportSchedules;
use App\Filament\Resources\ReportSchedules\Schemas\ReportScheduleForm;
use App\Filament\Resources\ReportSchedules\Tables\ReportSchedulesTable;
use App\Models\ReportSchedule;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use App\Filament\Concerns\HasPermissionAccess;

class ReportScheduleResource extends Resource
{
    use HasPermissionAccess;
    protected static ?string $model = ReportSchedule::class;

    protected static ?string $label = 'Report Schedules';

    protected static ?string $pluralLabel = 'Report Schedules';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowTopRightOnSquare;

    protected static ?int $navigationSort = 1111;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getNavigationGroup(): ?string
    {
        return 'Reports & Analytics';
    }

    public static function form(Schema $schema): Schema
    {
        return ReportScheduleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ReportSchedulesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListReportSchedules::route('/'),
            'create' => CreateReportSchedule::route('/create'),
            'edit' => EditReportSchedule::route('/{record}/edit'),
        ];
    }
}