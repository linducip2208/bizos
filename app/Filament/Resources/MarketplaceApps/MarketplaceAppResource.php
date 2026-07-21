<?php

namespace App\Filament\Resources\MarketplaceApps;

use App\Filament\Concerns\HasPermissionAccess;
use App\Filament\Resources\MarketplaceApps\Pages\CreateMarketplaceApp;
use App\Filament\Resources\MarketplaceApps\Pages\EditMarketplaceApp;
use App\Filament\Resources\MarketplaceApps\Pages\ListMarketplaceApps;
use App\Filament\Resources\MarketplaceApps\Schemas\MarketplaceAppForm;
use App\Filament\Resources\MarketplaceApps\Tables\MarketplaceAppsTable;
use App\Models\MarketplaceApp;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MarketplaceAppResource extends Resource
{
    use HasPermissionAccess;

    protected static ?string $model = MarketplaceApp::class;

    protected static ?string $label = 'Aplikasi Marketplace';

    protected static ?string $pluralLabel = 'Aplikasi Marketplace';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingBag;

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): string|null
    {
        return 'Platform';
    }

    public static function form(Schema $schema): Schema
    {
        return MarketplaceAppForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MarketplaceAppsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMarketplaceApps::route('/'),
            'create' => CreateMarketplaceApp::route('/create'),
            'edit' => EditMarketplaceApp::route('/{record}/edit'),
        ];
    }
}