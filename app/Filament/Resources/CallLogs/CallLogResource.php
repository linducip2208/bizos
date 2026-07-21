<?php

namespace App\Filament\Resources\CallLogs;

use App\Filament\Concerns\HasPermissionAccess;
use App\Filament\Resources\CallLogs\Pages\CreateCallLog;
use App\Filament\Resources\CallLogs\Pages\EditCallLog;
use App\Filament\Resources\CallLogs\Pages\ListCallLogs;
use App\Filament\Resources\CallLogs\Schemas\CallLogForm;
use App\Filament\Resources\CallLogs\Tables\CallLogsTable;
use App\Models\CallLog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CallLogResource extends Resource
{
    use HasPermissionAccess;

    protected static ?string $model = CallLog::class;

    public static function getNavigationGroup(): string|null
    {
        return '📈 Sales & CRM';
    }

    protected static ?string $label = 'Log Panggilan';

    protected static ?string $pluralLabel = 'Log Panggilan';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhone;

    protected static ?int $navigationSort = 419;

    public static function form(Schema $schema): Schema
    {
        return CallLogForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CallLogsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCallLogs::route('/'),
            'create' => CreateCallLog::route('/create'),
            'edit' => EditCallLog::route('/{record}/edit'),
        ];
    }
}
