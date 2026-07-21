<?php

namespace App\Filament\Resources\WasteLogs;

use App\Filament\Resources\WasteLogs\Pages;
use App\Models\WasteLog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use App\Filament\Concerns\HasPermissionAccess;
use Filament\Support\Icons\Heroicon;

class WasteLogResource extends Resource
{
    use HasPermissionAccess;

    protected static ?string $model = WasteLog::class;

    public static function getNavigationGroup(): string|null
    {
        return '?? Industri';
    }

    protected static ?string $label = 'Waste Log';

    protected static ?string $pluralLabel = 'Waste Log';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTrash;

    protected static ?int $navigationSort = 7;

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return \App\Filament\Resources\WasteLogs\Schemas\WasteLogForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return \App\Filament\Resources\WasteLogs\Tables\WasteLogTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListWasteLogs::route('/'),
            'create' => Pages\CreateWasteLog::route('/create'),
            'edit' => Pages\EditWasteLog::route('/{record}/edit'),
        ];
    }
}