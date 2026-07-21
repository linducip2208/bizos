<?php

namespace App\Filament\Resources\RabItems;

use App\Filament\Concerns\HasPermissionAccess;
use App\Filament\Resources\RabItems\Pages\CreateRabItem;
use App\Filament\Resources\RabItems\Pages\EditRabItem;
use App\Filament\Resources\RabItems\Pages\ListRabItems;
use App\Filament\Resources\RabItems\Schemas\RabItemForm;
use App\Filament\Resources\RabItems\Tables\RabItemTable;
use App\Models\RabItem;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class RabItemResource extends Resource
{
    use HasPermissionAccess;

    protected static ?string $model = RabItem::class;

    public static function getNavigationGroup(): string|null
    {
        return '🏗️ Konstruksi';
    }

    protected static ?string $label = 'Item RAB';
    protected static ?string $pluralLabel = 'Item RAB';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalculator;

    protected static ?int $navigationSort = 601;

    protected static ?string $recordTitleAttribute = 'description';

    public static function form(Schema $schema): Schema
    {
        return RabItemForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RabItemTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRabItems::route('/'),
            'create' => CreateRabItem::route('/create'),
            'edit' => EditRabItem::route('/{record}/edit'),
        ];
    }
}
