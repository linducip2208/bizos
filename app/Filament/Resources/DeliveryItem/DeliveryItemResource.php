<?php

namespace App\Filament\Resources\DeliveryItem;

use App\Filament\Resources\DeliveryItem\Pages\CreateDeliveryItem;
use App\Filament\Resources\DeliveryItem\Pages\EditDeliveryItem;
use App\Filament\Resources\DeliveryItem\Pages\ListDeliveryItems;
use App\Filament\Resources\DeliveryItem\Schemas\DeliveryItemForm;
use App\Filament\Resources\DeliveryItem\Tables\DeliveryItemTable;
use App\Models\DeliveryItem;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use App\Filament\Concerns\HasPermissionAccess;

class DeliveryItemResource extends Resource
{
    protected static bool $shouldRegisterNavigation = false;
    use HasPermissionAccess;

    protected static ?string $model = DeliveryItem::class;

    public static function getNavigationGroup(): string|null
    {
        return \App\Filament\Navigation\NavigationGroup::OPERATIONS->value;
    }

    protected static ?string $label = 'Delivery Items';

    protected static ?string $pluralLabel = 'Delivery Items';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTruck;

    protected static ?int $navigationSort = 9;

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return DeliveryItemForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DeliveryItemTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDeliveryItems::route('/'),
            'create' => CreateDeliveryItem::route('/create'),
            'edit' => EditDeliveryItem::route('/{record}/edit'),
        ];
    }
}