<?php

namespace App\Filament\Resources\ColdChainLog;

use App\Filament\Concerns\HasPermissionAccess;
use App\Filament\Resources\ColdChainLog\Pages\CreateColdChainLog;
use App\Filament\Resources\ColdChainLog\Pages\EditColdChainLog;
use App\Filament\Resources\ColdChainLog\Pages\ListColdChainLogs;
use App\Filament\Resources\ColdChainLog\Schemas\ColdChainLogForm;
use App\Filament\Resources\ColdChainLog\Tables\ColdChainLogTable;
use App\Models\ColdChainLog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ColdChainLogResource extends Resource
{
    protected static bool $shouldRegisterNavigation = false;
{
    use HasPermissionAccess;

    protected static ?string $model = ColdChainLog::class;

    public static function getNavigationGroup(): string|null
    {
        return '🏭 Industry';
    }

    protected static ?string $label = 'Cold Chain Log';

    protected static ?string $pluralLabel = 'Cold Chain Log';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSun;

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return ColdChainLogForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ColdChainLogTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListColdChainLogs::route('/'),
            'create' => CreateColdChainLog::route('/create'),
            'edit' => EditColdChainLog::route('/{record}/edit'),
        ];
    }
}