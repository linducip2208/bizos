<?php

namespace App\Filament\Resources\ReconciliationItem;

use App\Filament\Resources\ReconciliationItem\Pages\ListReconciliationItems;
use App\Filament\Resources\ReconciliationItem\Tables\ReconciliationItemsTable;
use App\Models\ReconciliationItem;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

use App\Filament\Concerns\HasPermissionAccess;
use Filament\Panel;

class ReconciliationItemResource extends Resource
{
    use HasPermissionAccess;

    protected static ?string $model = ReconciliationItem::class;

    public static function getSlug(?Panel $panel = null): string
    {
        return 'reconciliation-items';
    }

    public static function getNavigationGroup(): string|null
    {
        return '?? Finance & Accounting';
    }

    protected static ?string $label = 'Item Rekonsiliasi';

    protected static ?string $pluralLabel = 'Item Rekonsiliasi';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedListBullet;

    protected static ?int $navigationSort = 325;

    protected static ?string $recordTitleAttribute = 'type';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return ReconciliationItemsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListReconciliationItems::route('/'),
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