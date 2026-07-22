<?php

namespace App\Filament\Resources\EcommerceInventoryLog;

use App\Filament\Concerns\HasPermissionAccess;
use App\Filament\Resources\EcommerceInventoryLog\Pages\ListEcommerceInventoryLogs;
use App\Filament\Resources\EcommerceInventoryLog\Tables\EcommerceInventoryLogTable;
use App\Models\EcommerceInventoryLog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Filament\Support\Icons\Heroicon;

class EcommerceInventoryLogResource extends Resource
{
    protected static bool $shouldRegisterNavigation = false;
    use HasPermissionAccess;

    protected static ?string $model = EcommerceInventoryLog::class;

    public static function getNavigationGroup(): string|null
    {
        return '🏭 Industry';
    }

    protected static ?string $label = 'Log Inventori';

    protected static ?string $pluralLabel = 'Log Inventori';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocument;

    protected static ?int $navigationSort = 4;

    protected static ?string $recordTitleAttribute = 'id';

    public static function table(Table $table): Table
    {
        return EcommerceInventoryLogTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEcommerceInventoryLogs::route('/'),
        ];
    }
}