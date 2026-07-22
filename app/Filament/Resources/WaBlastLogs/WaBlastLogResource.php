<?php

namespace App\Filament\Resources\WaBlastLogs;

use App\Filament\Resources\WaBlastLogs\Pages\CreateWaBlastLog;
use App\Filament\Resources\WaBlastLogs\Pages\EditWaBlastLog;
use App\Filament\Resources\WaBlastLogs\Pages\ListWaBlastLogs;
use App\Filament\Resources\WaBlastLogs\Schemas\WaBlastLogForm;
use App\Filament\Resources\WaBlastLogs\Tables\WaBlastLogsTable;
use App\Models\WaBlastLog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;


use App\Filament\Concerns\HasPermissionAccess;
class WaBlastLogResource extends Resource
{
    protected static bool $shouldRegisterNavigation = false;
    use HasPermissionAccess;
    protected static ?string $model = WaBlastLog::class;

    public static function getNavigationGroup(): string|null
    {
        return '📈 Sales & CRM';
    }

    protected static ?string $label = 'Log Blast WA';

    protected static ?string $pluralLabel = 'Log Blast WA';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    protected static ?int $navigationSort = 412;

    protected static ?string $recordTitleAttribute = 'contact_name';

    public static function form(Schema $schema): Schema
    {
        return WaBlastLogForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WaBlastLogsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWaBlastLogs::route('/'),
            'create' => CreateWaBlastLog::route('/create'),
            'edit' => EditWaBlastLog::route('/{record}/edit'),
        ];
    }
}