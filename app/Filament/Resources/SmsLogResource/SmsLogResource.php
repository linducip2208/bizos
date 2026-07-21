<?php

namespace App\Filament\Resources\SmsLogResource;

use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use App\Filament\Concerns\HasPermissionAccess;
use App\Filament\Resources\SmsLogResource\Pages\ListSmsLogs;
use App\Filament\Resources\SmsLogResource\Tables\SmsLogTable;
use App\Models\SmsLog;

class SmsLogResource extends Resource
{
    use HasPermissionAccess;
    protected static ?string $model = SmsLog::class;
    public static function getNavigationGroup(): string|null { return 'Integrasi'; }
    protected static ?string $label = 'Log SMS';
    protected static ?string $pluralLabel = 'Log SMS';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedInboxArrowDown;
    protected static ?int $navigationSort = 6;
    protected static ?string $recordTitleAttribute = 'recipient';
    public static function form(Schema $schema): Schema { return $schema; }
    public static function table(Table $table): Table { return SmsLogTable::configure($table); }
    public static function getRelations(): array { return []; }
    public static function getPages(): array { return ['index' => ListSmsLogs::route('/')]; }
    public static function canCreate(): bool { return false; }
}
