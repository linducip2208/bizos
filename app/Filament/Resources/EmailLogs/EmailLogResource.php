<?php

namespace App\Filament\Resources\EmailLogs;

use App\Filament\Concerns\HasPermissionAccess;
use App\Filament\Resources\EmailLogs\Pages\CreateEmailLog;
use App\Filament\Resources\EmailLogs\Pages\EditEmailLog;
use App\Filament\Resources\EmailLogs\Pages\ListEmailLogs;
use App\Filament\Resources\EmailLogs\Schemas\EmailLogForm;
use App\Filament\Resources\EmailLogs\Tables\EmailLogsTable;
use App\Models\EmailLog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class EmailLogResource extends Resource
{
    use HasPermissionAccess;

    protected static ?string $model = EmailLog::class;

    public static function getNavigationGroup(): string|null
    {
        return '📈 Sales & CRM';
    }

    protected static ?string $label = 'Log Email';

    protected static ?string $pluralLabel = 'Log Email';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    protected static ?int $navigationSort = 420;

    public static function form(Schema $schema): Schema
    {
        return EmailLogForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EmailLogsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEmailLogs::route('/'),
            'create' => CreateEmailLog::route('/create'),
            'edit' => EditEmailLog::route('/{record}/edit'),
        ];
    }
}
