<?php

namespace App\Filament\Resources\DataMergeLogs;

use App\Filament\Concerns\HasPermissionAccess;
use App\Filament\Resources\DataMergeLogs\Pages\ListDataMergeLogs;
use App\Filament\Resources\DataMergeLogs\Pages\ViewDataMergeLog;
use App\Filament\Resources\DataMergeLogs\Schemas\DataMergeLogForm;
use App\Filament\Resources\DataMergeLogs\Tables\DataMergeLogsTable;
use App\Models\DataMergeLog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class DataMergeLogResource extends Resource
{
    use HasPermissionAccess;

    protected static ?string $model = DataMergeLog::class;

    protected static ?string $label = 'Log Penggabungan Data';

    protected static ?string $pluralLabel = 'Log Penggabungan Data';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowPath;

    protected static ?int $navigationSort = 447;

    public static function getNavigationGroup(): string|null
    {
        return '⚙️ System';
    }

    public static function form(Schema $schema): Schema
    {
        return DataMergeLogForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DataMergeLogsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDataMergeLogs::route('/'),
            'view' => ViewDataMergeLog::route('/{record}'),
        ];
    }
}
