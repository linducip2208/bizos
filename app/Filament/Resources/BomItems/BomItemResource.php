<?php

namespace App\Filament\Resources\BomItems;

use App\Filament\Resources\BomItems\Pages;
use App\Models\BomItem;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use App\Filament\Concerns\HasPermissionAccess;
use Filament\Support\Icons\Heroicon;

class BomItemResource extends Resource
{
    protected static bool $shouldRegisterNavigation = false;
    use HasPermissionAccess;

    protected static ?string $model = BomItem::class;

    public static function getNavigationGroup(): string|null
    {
        return \App\Filament\Navigation\NavigationGroup::OPERATIONS->value;
    }

    protected static ?string $label = 'BOM Items';

    protected static ?string $pluralLabel = 'BOM Items';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQueueList;

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return \App\Filament\Resources\BomItems\Schemas\BomItemForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return \App\Filament\Resources\BomItems\Tables\BomItemTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBomItems::route('/'),
            'create' => Pages\CreateBomItem::route('/create'),
            'edit' => Pages\EditBomItem::route('/{record}/edit'),
        ];
    }
}