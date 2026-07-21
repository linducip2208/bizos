<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BomItemResource\Pages;
use App\Models\BomItem;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use App\Filament\Concerns\HasPermissionAccess;
use Filament\Support\Icons\Heroicon;

class BomItemResource extends Resource
{
    use HasPermissionAccess;

    protected static ?string $model = BomItem::class;

    public static function getNavigationGroup(): string|null
    {
        return 'Manufaktur';
    }

    protected static ?string $label = 'Item BOM';

    protected static ?string $pluralLabel = 'Item BOM';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQueueList;

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return \App\Filament\Resources\BomItemResource\Schemas\BomItemForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return \App\Filament\Resources\BomItemResource\Tables\BomItemTable::configure($table);
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
