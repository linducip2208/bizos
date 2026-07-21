<?php

namespace App\Filament\Resources\CanteenMenus;

use App\Filament\Resources\CanteenMenus\Pages\CreateCanteenMenu;
use App\Filament\Resources\CanteenMenus\Pages\EditCanteenMenu;
use App\Filament\Resources\CanteenMenus\Pages\ListCanteenMenus;
use App\Filament\Resources\CanteenMenus\Schemas\CanteenMenuForm;
use App\Filament\Resources\CanteenMenus\Tables\CanteenMenusTable;
use App\Models\CanteenMenu;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;


use App\Filament\Concerns\HasPermissionAccess;
class CanteenMenuResource extends Resource
{
    use HasPermissionAccess;
    protected static ?string $model = CanteenMenu::class;

    public static function getNavigationGroup(): string|null
    {
        return 'HRM';
    }

    protected static ?string $label = 'Menu Kantin';

    protected static ?string $pluralLabel = 'Menu Kantin';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingCart;

    protected static ?int $navigationSort = 120;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return CanteenMenuForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CanteenMenusTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCanteenMenus::route('/'),
            'create' => CreateCanteenMenu::route('/create'),
            'edit' => EditCanteenMenu::route('/{record}/edit'),
        ];
    }
}
