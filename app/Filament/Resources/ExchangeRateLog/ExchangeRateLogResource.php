<?php

namespace App\Filament\Resources\ExchangeRateLog;

use App\Filament\Resources\ExchangeRateLog\Pages\ListExchangeRateLogs;
use App\Filament\Resources\ExchangeRateLog\Tables\ExchangeRateLogsTable;
use App\Models\ExchangeRateLog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Support\Icons\Heroicon;
use Filament\Schemas\Schema;

use App\Filament\Concerns\HasPermissionAccess;
use Filament\Panel;

class ExchangeRateLogResource extends Resource
{
    use HasPermissionAccess;

    protected static ?string $model = ExchangeRateLog::class;

    public static function getSlug(?Panel $panel = null): string
    {
        return 'exchange-rate-logs';
    }

    public static function getNavigationGroup(): string|null
    {
        return '?? Finance & Accounting';
    }

    protected static ?string $label = 'Riwayat Kurs';

    protected static ?string $pluralLabel = 'Riwayat Kurs';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowTrendingUp;

    protected static ?int $navigationSort = 321;

    protected static ?string $recordTitleAttribute = 'rate_date';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return ExchangeRateLogsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListExchangeRateLogs::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }
}